<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Importer\AttributeImporter;
use RZ\Roadiz\CoreBundle\Importer\EntityImporterInterface;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Importer\NodeTypesImporter;
use RZ\Roadiz\CoreBundle\Importer\RolesImporter;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use RZ\Roadiz\CoreBundle\Importer\TagsImporter;
use RZ\Roadiz\CoreBundle\Theme\ThemeGenerator;
use RZ\Roadiz\CoreBundle\Theme\ThemeInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

/**
 * Command line utils for managing themes from terminal.
 */
class ThemeInstallCommand extends Command
{
    protected SymfonyStyle $io;
    private bool $dryRun = false;
    protected string $projectDir;
    protected ThemeGenerator $themeGenerator;
    protected NodeTypesImporter $nodeTypesImporter;
    protected TagsImporter $tagsImporter;
    protected SettingsImporter $settingsImporter;
    protected RolesImporter $rolesImporter;
    protected GroupsImporter $groupsImporter;
    protected AttributeImporter $attributeImporter;

    /**
     * @param string $projectDir
     * @param ThemeGenerator $themeGenerator
     * @param NodeTypesImporter $nodeTypesImporter
     * @param TagsImporter $tagsImporter
     * @param SettingsImporter $settingsImporter
     * @param RolesImporter $rolesImporter
     * @param GroupsImporter $groupsImporter
     * @param AttributeImporter $attributeImporter
     */
    public function __construct(
        string $projectDir,
        ThemeGenerator $themeGenerator,
        NodeTypesImporter $nodeTypesImporter,
        TagsImporter $tagsImporter,
        SettingsImporter $settingsImporter,
        RolesImporter $rolesImporter,
        GroupsImporter $groupsImporter,
        AttributeImporter $attributeImporter
    ) {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->themeGenerator = $themeGenerator;
        $this->nodeTypesImporter = $nodeTypesImporter;
        $this->tagsImporter = $tagsImporter;
        $this->settingsImporter = $settingsImporter;
        $this->rolesImporter = $rolesImporter;
        $this->groupsImporter = $groupsImporter;
        $this->attributeImporter = $attributeImporter;
    }

    protected function configure()
    {
        $this->setName('themes:install')
            ->setDescription('Manage themes installation')
            ->addArgument(
                'classname',
                InputArgument::REQUIRED,
                'Main theme classname (Use / instead of \\ and do not forget starting slash) or path to config.yml'
            )
            ->addOption(
                'data',
                null,
                InputOption::VALUE_NONE,
                'Import default data (node-types, roles, settings and tags)'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do nothing, only print information.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dry-run')) {
            $this->dryRun = true;
        }
        $this->io = new SymfonyStyle($input, $output);
        $themeInfo = null;

        /*
         * Test if Classname is not a valid yaml file before using Theme
         */
        if ((new UnicodeString($input->getArgument('classname')))->endsWith('config.yml')) {
            $classname = realpath($input->getArgument('classname'));
            if (file_exists($classname)) {
                $this->io->note('Install assets directly from file: '. $classname);
                $themeConfigPath = $classname;
            } else {
                $this->io->error($classname .' configuration file is not readable.');
                return 1;
            }
        } else {
            /*
             * Replace slash by anti-slashes
             */
            $classname = str_replace('/', '\\', $input->getArgument('classname'));
            $themeInfo = new ThemeInfo($classname, $this->projectDir);
            $themeConfigPath = $themeInfo->getThemePath() . '/config.yml';
            if (!$themeInfo->isValid()) {
                throw new RuntimeException($themeInfo->getClassname() . ' is not a valid Roadiz theme.');
            }
            if (!file_exists($themeConfigPath)) {
                $this->io->warning($themeInfo->getName() .' theme does not have any configuration.');
                return 1;
            }
        }

        if ($output->isVeryVerbose() && null !== $themeInfo) {
            $this->io->writeln('Theme name is: <info>'. $themeInfo->getName() .'</info>.');
            $this->io->writeln('Theme assets are located in <info>'. $themeInfo->getThemePath() .'/static</info>.');
        }

        if ($input->getOption('data')) {
            $this->importThemeData($themeInfo, $themeConfigPath);
        } else {
            $this->io->note(
                'Roadiz themes are no more registered into database. ' .
                'You should use --data or --nodes option.'
            );
        }
        return 0;
    }

    protected function importThemeData(ThemeInfo $themeInfo, string $themeConfigPath)
    {
        $data = $this->getThemeConfig($themeConfigPath);

        if (false !== $data && isset($data["importFiles"])) {
            if (isset($data["importFiles"]['groups'])) {
                foreach ($data["importFiles"]['groups'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->groupsImporter);
                }
            }
            if (isset($data["importFiles"]['roles'])) {
                foreach ($data["importFiles"]['roles'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->rolesImporter);
                }
            }
            if (isset($data["importFiles"]['settings'])) {
                foreach ($data["importFiles"]['settings'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->settingsImporter);
                }
            }
            if (isset($data["importFiles"]['nodetypes'])) {
                foreach ($data["importFiles"]['nodetypes'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->nodeTypesImporter);
                }
            }
            if (isset($data["importFiles"]['tags'])) {
                foreach ($data["importFiles"]['tags'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->tagsImporter);
                }
            }
            if (isset($data["importFiles"]['attributes'])) {
                foreach ($data["importFiles"]['attributes'] as $filename) {
                    $this->importFile($themeInfo, $filename, $this->attributeImporter);
                }
            }
            if ($this->io->isVeryVerbose()) {
                $this->io->note(
                    'You should do a `bin/roadiz generate:nsentities`' .
                    ' to regenerate your node-types source classes, ' .
                    'and a `bin/roadiz orm:schema-tool:update --dump-sql --force` ' .
                    'to apply your changes into database.'
                );
            }
        } else {
            $this->io->warning('Config file "' . $themeConfigPath . '" has no data to import.');
        }
    }

    /**
     * @param ThemeInfo|null $themeInfo
     * @param string $filename
     * @param EntityImporterInterface $importer
     */
    protected function importFile(?ThemeInfo $themeInfo, string $filename, EntityImporterInterface $importer): void
    {
        if (null !== $themeInfo) {
            $file = new File($themeInfo->getThemePath() . "/" . $filename);
        } else {
            $file = new File(realpath($filename));
        }
        if (!$this->dryRun) {
            try {
                $importer->import(file_get_contents($file->getPathname()));

                /** @var ManagerRegistry $managerRegistry */
                $managerRegistry = $this->getHelper('doctrine')->getManagerRegistry();
                $managerRegistry->getManager()->flush();
                $this->io->writeln(
                    '* <info>' . $file->getPathname() . '</info> file has been imported.'
                );
                return;
            } catch (EntityAlreadyExistsException $e) {
                $this->io->writeln(
                    '* <info>' . $file->getPathname() . '</info>' .
                    ' <error>has NOT been imported ('.$e->getMessage().')</error>.'
                );
            }
        }
        $this->io->writeln(
            '* <info>' . $file->getPathname() . '</info> file has been imported.'
        );
    }

    /**
     * @return array
     */
    protected function getThemeConfig(string $themeConfigPath)
    {
        return Yaml::parse(file_get_contents($themeConfigPath));
    }
}
