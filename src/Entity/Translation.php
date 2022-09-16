<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translations describe language locales to be used by Nodes,
 * Tags, UrlAliases and Documents.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\TranslationRepository")
 * @ORM\Table(name="translations", indexes={
 *     @ORM\Index(columns={"available"}),
 *     @ORM\Index(columns={"default_translation"}),
 *     @ORM\Index(columns={"created_at"}),
 *     @ORM\Index(columns={"updated_at"}),
 *     @ORM\Index(columns={"available", "default_translation"}),
 *     @ORM\Index(columns={"available", "locale"}),
 *     @ORM\Index(columns={"available", "override_locale"})
 * })
 * @ApiFilter(\ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(BaseFilter\OrderFilter::class, properties={
 *     "createdAt",
 *     "updatedAt",
 *     "locale",
 *     "available",
 *     "defaultTranslation"
 * })
 * @ApiFilter(BaseFilter\BooleanFilter::class, properties={
 *     "available",
 *     "defaultTranslation"
 * })
 * @ApiFilter(BaseFilter\SearchFilter::class, properties={
 *     "locale": "exact",
 *     "name": "exact"
 * })
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"locale"})
 * @UniqueEntity(fields={"overrideLocale"})
 */
class Translation extends AbstractDateTimed implements TranslationInterface
{
    /**
     * Associates locales to pretty languages names.
     *
     * @var array
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    public static array $availableLocales = [
        'af_NA' => "Afrikaans (Namibia)",
        'af_ZA' => "Afrikaans (South Africa)",
        'af' => "Afrikaans",
        'ak_GH' => "Akan (Ghana)",
        'ak' => "Akan",
        'sq_AL' => "Albanian (Albania)",
        'sq' => "Albanian",
        'am_ET' => "Amharic (Ethiopia)",
        'am' => "Amharic",
        'ar_DZ' => "Arabic (Algeria)",
        'ar_BH' => "Arabic (Bahrain)",
        'ar_EG' => "Arabic (Egypt)",
        'ar_IQ' => "Arabic (Iraq)",
        'ar_JO' => "Arabic (Jordan)",
        'ar_KW' => "Arabic (Kuwait)",
        'ar_LB' => "Arabic (Lebanon)",
        'ar_LY' => "Arabic (Libya)",
        'ar_MA' => "Arabic (Morocco)",
        'ar_OM' => "Arabic (Oman)",
        'ar_QA' => "Arabic (Qatar)",
        'ar_SA' => "Arabic (Saudi Arabia)",
        'ar_SD' => "Arabic (Sudan)",
        'ar_SY' => "Arabic (Syria)",
        'ar_TN' => "Arabic (Tunisia)",
        'ar_AE' => "Arabic (United Arab Emirates)",
        'ar_YE' => "Arabic (Yemen)",
        'ar' => "Arabic",
        'hy_AM' => "Armenian (Armenia)",
        'hy' => "Armenian",
        'as_IN' => "Assamese (India)",
        'as' => "Assamese",
        'asa_TZ' => "Asu (Tanzania)",
        'asa' => "Asu",
        'az_Cyrl' => "Azerbaijani (Cyrillic)",
        'az_Cyrl_AZ' => "Azerbaijani (Cyrillic, Azerbaijan)",
        'az_Latn' => "Azerbaijani (Latin)",
        'az_Latn_AZ' => "Azerbaijani (Latin, Azerbaijan)",
        'az' => "Azerbaijani",
        'bm_ML' => "Bambara (Mali)",
        'bm' => "Bambara",
        'eu_ES' => "Basque (Spain)",
        'eu' => "Basque",
        'be_BY' => "Belarusian (Belarus)",
        'be' => "Belarusian",
        'bem_ZM' => "Bemba (Zambia)",
        'bem' => "Bemba",
        'bez_TZ' => "Bena (Tanzania)",
        'bez' => "Bena",
        'bn_BD' => "Bengali (Bangladesh)",
        'bn_IN' => "Bengali (India)",
        'bn' => "Bengali",
        'bs_BA' => "Bosnian (Bosnia and Herzegovina)",
        'bs' => "Bosnian",
        'bg_BG' => "Bulgarian (Bulgaria)",
        'bg' => "Bulgarian",
        'my_MM' => "Burmese (Myanmar [Burma])",
        'my' => "Burmese",
        'ca_ES' => "Catalan (Spain)",
        'ca' => "Catalan",
        'tzm_Latn' => "Central Morocco Tamazight (Latin)",
        'tzm_Latn_MA' => "Central Morocco Tamazight (Latin, Morocco)",
        'tzm' => "Central Morocco Tamazight",
        'chr_US' => "Cherokee (United States)",
        'chr' => "Cherokee",
        'cgg_UG' => "Chiga (Uganda)",
        'cgg' => "Chiga",
        'zh_Hans' => "Chinese (Simplified Han)",
        'zh_Hans_CN' => "Chinese (Simplified Han, China)",
        'zh_Hans_HK' => "Chinese (Simplified Han, Hong Kong SAR China)",
        'zh_Hans_MO' => "Chinese (Simplified Han, Macau SAR China)",
        'zh_Hans_SG' => "Chinese (Simplified Han, Singapore)",
        'zh_Hant' => "Chinese (Traditional Han)",
        'zh_Hant_HK' => "Chinese (Traditional Han, Hong Kong SAR China)",
        'zh_Hant_MO' => "Chinese (Traditional Han, Macau SAR China)",
        'zh_Hant_TW' => "Chinese (Traditional Han, Taiwan)",
        'zh' => "Chinese",
        'kw_GB' => "Cornish (United Kingdom)",
        'kw' => "Cornish",
        'hr_HR' => "Croatian (Croatia)",
        'hr' => "Croatian",
        'cs_CZ' => "Czech (Czech Republic)",
        'cs' => "Czech",
        'da_DK' => "Danish (Denmark)",
        'da' => "Danish",
        'nl_BE' => "Dutch (Belgium)",
        'nl_NL' => "Dutch (Netherlands)",
        'nl' => "Dutch",
        'ebu_KE' => "Embu (Kenya)",
        'ebu' => "Embu",
        'en_AS' => "English (American Samoa)",
        'en_AU' => "English (Australia)",
        'en_BE' => "English (Belgium)",
        'en_BZ' => "English (Belize)",
        'en_BW' => "English (Botswana)",
        'en_CA' => "English (Canada)",
        'en_GU' => "English (Guam)",
        'en_HK' => "English (Hong Kong SAR China)",
        'en_IN' => "English (India)",
        'en_IE' => "English (Ireland)",
        'en_JM' => "English (Jamaica)",
        'en_MT' => "English (Malta)",
        'en_MH' => "English (Marshall Islands)",
        'en_MU' => "English (Mauritius)",
        'en_NA' => "English (Namibia)",
        'en_NZ' => "English (New Zealand)",
        'en_MP' => "English (Northern Mariana Islands)",
        'en_PK' => "English (Pakistan)",
        'en_PH' => "English (Philippines)",
        'en_SG' => "English (Singapore)",
        'en_ZA' => "English (South Africa)",
        'en_TT' => "English (Trinidad and Tobago)",
        'en_UM' => "English (U.S. Minor Outlying Islands)",
        'en_VI' => "English (U.S. Virgin Islands)",
        'en_GB' => "English (United Kingdom)",
        'en_US' => "English (United States)",
        'en_ZW' => "English (Zimbabwe)",
        'en' => "English",
        'eo' => "Esperanto",
        'et_EE' => "Estonian (Estonia)",
        'et' => "Estonian",
        'ee_GH' => "Ewe (Ghana)",
        'ee_TG' => "Ewe (Togo)",
        'ee' => "Ewe",
        'fo_FO' => "Faroese (Faroe Islands)",
        'fo' => "Faroese",
        'fil_PH' => "Filipino (Philippines)",
        'fil' => "Filipino",
        'fi_FI' => "Finnish (Finland)",
        'fi' => "Finnish",
        'fr_BE' => "French (Belgium)",
        'fr_BJ' => "French (Benin)",
        'fr_BF' => "French (Burkina Faso)",
        'fr_BI' => "French (Burundi)",
        'fr_CM' => "French (Cameroon)",
        'fr_CA' => "French (Canada)",
        'fr_CF' => "French (Central African Republic)",
        'fr_TD' => "French (Chad)",
        'fr_KM' => "French (Comoros)",
        'fr_CG' => "French (Congo - Brazzaville)",
        'fr_CD' => "French (Congo - Kinshasa)",
        'fr_CI' => "French (Côte d’Ivoire)",
        'fr_DJ' => "French (Djibouti)",
        'fr_GQ' => "French (Equatorial Guinea)",
        'fr_FR' => "French (France)",
        'fr_GA' => "French (Gabon)",
        'fr_GP' => "French (Guadeloupe)",
        'fr_GN' => "French (Guinea)",
        'fr_LU' => "French (Luxembourg)",
        'fr_MG' => "French (Madagascar)",
        'fr_ML' => "French (Mali)",
        'fr_MQ' => "French (Martinique)",
        'fr_MC' => "French (Monaco)",
        'fr_NE' => "French (Niger)",
        'fr_RW' => "French (Rwanda)",
        'fr_RE' => "French (Réunion)",
        'fr_BL' => "French (Saint Barthélemy)",
        'fr_MF' => "French (Saint Martin)",
        'fr_SN' => "French (Senegal)",
        'fr_CH' => "French (Switzerland)",
        'fr_TG' => "French (Togo)",
        'fr' => "French",
        'ff_SN' => "Fulah (Senegal)",
        'ff' => "Fulah",
        'gl_ES' => "Galician (Spain)",
        'gl' => "Galician",
        'lg_UG' => "Ganda (Uganda)",
        'lg' => "Ganda",
        'ka_GE' => "Georgian (Georgia)",
        'ka' => "Georgian",
        'de_AT' => "German (Austria)",
        'de_BE' => "German (Belgium)",
        'de_DE' => "German (Germany)",
        'de_LI' => "German (Liechtenstein)",
        'de_LU' => "German (Luxembourg)",
        'de_CH' => "German (Switzerland)",
        'de' => "German",
        'el_CY' => "Greek (Cyprus)",
        'el_GR' => "Greek (Greece)",
        'el' => "Greek",
        'gu_IN' => "Gujarati (India)",
        'gu' => "Gujarati",
        'guz_KE' => "Gusii (Kenya)",
        'guz' => "Gusii",
        'ha_Latn' => "Hausa (Latin)",
        'ha_Latn_GH' => "Hausa (Latin, Ghana)",
        'ha_Latn_NE' => "Hausa (Latin, Niger)",
        'ha_Latn_NG' => "Hausa (Latin, Nigeria)",
        'ha' => "Hausa",
        'haw_US' => "Hawaiian (United States)",
        'haw' => "Hawaiian",
        'he_IL' => "Hebrew (Israel)",
        'he' => "Hebrew",
        'hi_IN' => "Hindi (India)",
        'hi' => "Hindi",
        'hu_HU' => "Hungarian (Hungary)",
        'hu' => "Hungarian",
        'is_IS' => "Icelandic (Iceland)",
        'is' => "Icelandic",
        'ig_NG' => "Igbo (Nigeria)",
        'ig' => "Igbo",
        'id_ID' => "Indonesian (Indonesia)",
        'id' => "Indonesian",
        'ga_IE' => "Irish (Ireland)",
        'ga' => "Irish",
        'it_IT' => "Italian (Italy)",
        'it_CH' => "Italian (Switzerland)",
        'it' => "Italian",
        'ja_JP' => "Japanese (Japan)",
        'ja' => "Japanese",
        'kea_CV' => "Kabuverdianu (Cape Verde)",
        'kea' => "Kabuverdianu",
        'kab_DZ' => "Kabyle (Algeria)",
        'kab' => "Kabyle",
        'kl_GL' => "Kalaallisut (Greenland)",
        'kl' => "Kalaallisut",
        'kln_KE' => "Kalenjin (Kenya)",
        'kln' => "Kalenjin",
        'kam_KE' => "Kamba (Kenya)",
        'kam' => "Kamba",
        'kn_IN' => "Kannada (India)",
        'kn' => "Kannada",
        'kk_Cyrl' => "Kazakh (Cyrillic)",
        'kk_Cyrl_KZ' => "Kazakh (Cyrillic, Kazakhstan)",
        'kk' => "Kazakh",
        'km_KH' => "Khmer (Cambodia)",
        'km' => "Khmer",
        'ki_KE' => "Kikuyu (Kenya)",
        'ki' => "Kikuyu",
        'rw_RW' => "Kinyarwanda (Rwanda)",
        'rw' => "Kinyarwanda",
        'kok_IN' => "Konkani (India)",
        'kok' => "Konkani",
        'ko_KR' => "Korean (South Korea)",
        'ko' => "Korean",
        'khq_ML' => "Koyra Chiini (Mali)",
        'khq' => "Koyra Chiini",
        'ses_ML' => "Koyraboro Senni (Mali)",
        'ses' => "Koyraboro Senni",
        'lag_TZ' => "Langi (Tanzania)",
        'lag' => "Langi",
        'lv_LV' => "Latvian (Latvia)",
        'lv' => "Latvian",
        'lt_LT' => "Lithuanian (Lithuania)",
        'lt' => "Lithuanian",
        'luo_KE' => "Luo (Kenya)",
        'luo' => "Luo",
        'luy_KE' => "Luyia (Kenya)",
        'luy' => "Luyia",
        'mk_MK' => "Macedonian (Macedonia)",
        'mk' => "Macedonian",
        'jmc_TZ' => "Machame (Tanzania)",
        'jmc' => "Machame",
        'kde_TZ' => "Makonde (Tanzania)",
        'kde' => "Makonde",
        'mg_MG' => "Malagasy (Madagascar)",
        'mg' => "Malagasy",
        'ms_BN' => "Malay (Brunei)",
        'ms_MY' => "Malay (Malaysia)",
        'ms' => "Malay",
        'ml_IN' => "Malayalam (India)",
        'ml' => "Malayalam",
        'mt_MT' => "Maltese (Malta)",
        'mt' => "Maltese",
        'gv_GB' => "Manx (United Kingdom)",
        'gv' => "Manx",
        'mr_IN' => "Marathi (India)",
        'mr' => "Marathi",
        'mas_KE' => "Masai (Kenya)",
        'mas_TZ' => "Masai (Tanzania)",
        'mas' => "Masai",
        'mer_KE' => "Meru (Kenya)",
        'mer' => "Meru",
        'mfe_MU' => "Morisyen (Mauritius)",
        'mfe' => "Morisyen",
        'naq_NA' => "Nama (Namibia)",
        'naq' => "Nama",
        'ne_IN' => "Nepali (India)",
        'ne_NP' => "Nepali (Nepal)",
        'ne' => "Nepali",
        'nd_ZW' => "North Ndebele (Zimbabwe)",
        'nd' => "North Ndebele",
        'nb_NO' => "Norwegian Bokmål (Norway)",
        'nb' => "Norwegian Bokmål",
        'nn_NO' => "Norwegian Nynorsk (Norway)",
        'nn' => "Norwegian Nynorsk",
        'nyn_UG' => "Nyankole (Uganda)",
        'nyn' => "Nyankole",
        'or_IN' => "Oriya (India)",
        'or' => "Oriya",
        'om_ET' => "Oromo (Ethiopia)",
        'm_KE' => "Oromo (Kenya)",
        'om' => "Oromo",
        'ps_AF' => "Pashto (Afghanistan)",
        'ps' => "Pashto",
        'fa_AF' => "Persian (Afghanistan)",
        'fa_IR' => "Persian (Iran)",
        'fa' => "Persian",
        'pl_PL' => "Polish (Poland)",
        'pl' => "Polish",
        'pt_BR' => "Portuguese (Brazil)",
        'pt_GW' => "Portuguese (Guinea-Bissau)",
        'pt_MZ' => "Portuguese (Mozambique)",
        'pt_PT' => "Portuguese (Portugal)",
        'pt' => "Portuguese",
        'pa_Arab' => "Punjabi (Arabic)",
        'pa_Arab_PK' => "Punjabi (Arabic, Pakistan)",
        'pa_Guru' => "Punjabi (Gurmukhi)",
        'pa_Guru_IN' => "Punjabi (Gurmukhi, India)",
        'pa' => "Punjabi",
        'ro_MD' => "Romanian (Moldova)",
        'ro_RO' => "Romanian (Romania)",
        'ro' => "Romanian",
        'rm_CH' => "Romansh (Switzerland)",
        'rm' => "Romansh",
        'rof_TZ' => "Rombo (Tanzania)",
        'rof' => "Rombo",
        'ru_MD' => "Russian (Moldova)",
        'ru_RU' => "Russian (Russia)",
        'ru_UA' => "Russian (Ukraine)",
        'ru' => "Russian",
        'rwk_TZ' => "Rwa (Tanzania)",
        'rwk' => "Rwa",
        'saq_KE' => "Samburu (Kenya)",
        'saq' => "Samburu",
        'sg_CF' => "Sango (Central African Republic)",
        'sg' => "Sango",
        'seh_MZ' => "Sena (Mozambique)",
        'seh' => "Sena",
        'sr_Cyrl' => "Serbian (Cyrillic)",
        'sr_Cyrl_BA' => "Serbian (Cyrillic, Bosnia and Herzegovina)",
        'sr_Cyrl_ME' => "Serbian (Cyrillic, Montenegro)",
        'sr_Cyrl_RS' => "Serbian (Cyrillic, Serbia)",
        'sr_Latn' => "Serbian (Latin)",
        'sr_Latn_BA' => "Serbian (Latin, Bosnia and Herzegovina)",
        'sr_Latn_ME' => "Serbian (Latin, Montenegro)",
        'sr_Latn_RS' => "Serbian (Latin, Serbia)",
        'sr' => "Serbian",
        'sn_ZW' => "Shona (Zimbabwe)",
        'sn' => "Shona",
        'ii_CN' => "Sichuan Yi (China)",
        'ii' => "Sichuan Yi",
        'si_LK' => "Sinhala (Sri Lanka)",
        'si' => "Sinhala",
        'sk_SK' => "Slovak (Slovakia)",
        'sk' => "Slovak",
        'sl_SI' => "Slovenian (Slovenia)",
        'sl' => "Slovenian",
        'xog_UG' => "Soga (Uganda)",
        'xog' => "Soga",
        'so_DJ' => "Somali (Djibouti)",
        'so_ET' => "Somali (Ethiopia)",
        'so_KE' => "Somali (Kenya)",
        'so_SO' => "Somali (Somalia)",
        'so' => "Somali",
        'es_AR' => "Spanish (Argentina)",
        'es_BO' => "Spanish (Bolivia)",
        'es_CL' => "Spanish (Chile)",
        'es_CO' => "Spanish (Colombia)",
        'es_CR' => "Spanish (Costa Rica)",
        'es_DO' => "Spanish (Dominican Republic)",
        'es_EC' => "Spanish (Ecuador)",
        'es_SV' => "Spanish (El Salvador)",
        'es_GQ' => "Spanish (Equatorial Guinea)",
        'es_GT' => "Spanish (Guatemala)",
        'es_HN' => "Spanish (Honduras)",
        'es_419' => "Spanish (Latin America)",
        'es_MX' => "Spanish (Mexico)",
        'es_NI' => "Spanish (Nicaragua)",
        'es_PA' => "Spanish (Panama)",
        'es_PY' => "Spanish (Paraguay)",
        'es_PE' => "Spanish (Peru)",
        'es_PR' => "Spanish (Puerto Rico)",
        'es_ES' => "Spanish (Spain)",
        'es_US' => "Spanish (United States)",
        'es_UY' => "Spanish (Uruguay)",
        'es_VE' => "Spanish (Venezuela)",
        'es' => "Spanish",
        'sw_KE' => "Swahili (Kenya)",
        'sw_TZ' => "Swahili (Tanzania)",
        'sw' => "Swahili",
        'sv_FI' => "Swedish (Finland)",
        'sv_SE' => "Swedish (Sweden)",
        'sv' => "Swedish",
        'gsw_CH' => "Swiss German (Switzerland)",
        'gsw' => "Swiss German",
        'shi_Latn' => "Tachelhit (Latin)",
        'shi_Latn_MA' => "Tachelhit (Latin, Morocco)",
        'shi_Tfng' => "Tachelhit (Tifinagh)",
        'shi_Tfng_MA' => "Tachelhit (Tifinagh, Morocco)",
        'shi' => "Tachelhit",
        'dav_KE' => "Taita (Kenya)",
        'dav' => "Taita",
        'ta_IN' => "Tamil (India)",
        'ta_LK' => "Tamil (Sri Lanka)",
        'ta' => "Tamil",
        'te_IN' => "Telugu (India)",
        'te' => "Telugu",
        'teo_KE' => "Teso (Kenya)",
        'teo_UG' => "Teso (Uganda)",
        'teo' => "Teso",
        'th_TH' => "Thai (Thailand)",
        'th' => "Thai",
        'bo_CN' => "Tibetan (China)",
        'bo_IN' => "Tibetan (India)",
        'bo' => "Tibetan",
        'ti_ER' => "Tigrinya (Eritrea)",
        'ti_ET' => "Tigrinya (Ethiopia)",
        'ti' => "Tigrinya",
        'to_TO' => "Tonga (Tonga)",
        'to' => "Tonga",
        'tr_TR' => "Turkish (Turkey)",
        'tr' => "Turkish",
        'uk_UA' => "Ukrainian (Ukraine)",
        'uk' => "Ukrainian",
        'ur_IN' => "Urdu (India)",
        'ur_PK' => "Urdu (Pakistan)",
        'ur' => "Urdu",
        'uz_Arab' => "Uzbek (Arabic)",
        'uz_Arab_AF' => "Uzbek (Arabic, Afghanistan)",
        'uz_Cyrl' => "Uzbek (Cyrillic)",
        'uz_Cyrl_UZ' => "Uzbek (Cyrillic, Uzbekistan)",
        'uz_Latn' => "Uzbek (Latin)",
        'uz_Latn_UZ' => "Uzbek (Latin, Uzbekistan)",
        'uz' => "Uzbek",
        'vi_VN' => "Vietnamese (Vietnam)",
        'vi' => "Vietnamese",
        'vun_TZ' => "Vunjo (Tanzania)",
        'vun' => "Vunjo",
        'cy_GB' => "Welsh (United Kingdom)",
        'cy' => "Welsh",
        'yo_NG' => "Yoruba (Nigeria)",
        'yo' => "Yoruba",
        'zu_ZA' => "Zulu (South Africa)",
        'zu' => "Zulu",
    ];
    /**
     * @var array
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    public static array $rtlLanguages = [
        'ar_DZ' => "Arabic (Algeria)",
        'ar_BH' => "Arabic (Bahrain)",
        'ar_EG' => "Arabic (Egypt)",
        'ar_IQ' => "Arabic (Iraq)",
        'ar_JO' => "Arabic (Jordan)",
        'ar_KW' => "Arabic (Kuwait)",
        'ar_LB' => "Arabic (Lebanon)",
        'ar_LY' => "Arabic (Libya)",
        'ar_MA' => "Arabic (Morocco)",
        'ar_OM' => "Arabic (Oman)",
        'ar_QA' => "Arabic (Qatar)",
        'ar_SA' => "Arabic (Saudi Arabia)",
        'ar_SD' => "Arabic (Sudan)",
        'ar_SY' => "Arabic (Syria)",
        'ar_TN' => "Arabic (Tunisia)",
        'ar_AE' => "Arabic (United Arab Emirates)",
        'ar_YE' => "Arabic (Yemen)",
        'ar' => "Arabic",
        'he_IL' => "Hebrew (Israel)",
        'he' => "Hebrew",
        'ur_IN' => "Urdu (India)",
        'ur_PK' => "Urdu (Pakistan)",
        'fa_AF' => "Persian (Afghanistan)",
        'fa_IR' => "Persian (Iran)",
        'fa' => "Persian",
    ];
    /**
     * @ORM\OneToMany(targetEntity="DocumentTranslation", mappedBy="translation", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var Collection<DocumentTranslation>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $documentTranslations;
    /**
     * @ORM\OneToMany(targetEntity="FolderTranslation", mappedBy="translation", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var Collection<FolderTranslation>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $folderTranslations;
    /**
     * Language locale
     *
     * fr or en for example
     *
     * @var string
     * @ORM\Column(type="string", unique=true, length=10)
     * @Serializer\Groups({"translation", "document", "nodes_sources", "tag", "attribute", "folder", "log_sources"})
     * @SymfonySerializer\Groups({"translation", "document", "nodes_sources", "tag", "attribute", "folder", "log_sources"})
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max=10)
     */
    private string $locale = '';
    /**
     * @var string|null
     * @ORM\Column(type="string", name="override_locale", length=10, unique=true, nullable=true)
     * @Serializer\Groups({"translation", "document", "nodes_sources", "tag", "attribute", "folder"})
     * @SymfonySerializer\Groups({"translation", "document", "nodes_sources", "tag", "attribute", "folder"})
     * @Serializer\Type("string")
     * @Assert\Length(max=10)
     */
    private ?string $overrideLocale = null;
    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"translation"})
     * @SymfonySerializer\Groups({"translation"})
     * @Serializer\Type("string")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(max=250)
     */
    private string $name = '';
    /**
     * @var bool
     * @ORM\Column(name="default_translation", type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"translation"})
     * @SymfonySerializer\Groups({"translation"})
     * @Serializer\Type("bool")
     */
    private bool $defaultTranslation = false;
    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"translation"})
     * @SymfonySerializer\Groups({"translation"})
     * @Serializer\Type("bool")
     */
    private bool $available = true;
    /**
     * @ORM\OneToMany(targetEntity="NodesSources", mappedBy="translation", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var Collection<NodesSources>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    private Collection $nodeSources;
    /**
     * @ORM\OneToMany(targetEntity="TagTranslation", mappedBy="translation", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var Collection<TagTranslation>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    private Collection $tagTranslations;

    public function __construct()
    {
        $this->nodeSources = new ArrayCollection();
        $this->tagTranslations = new ArrayCollection();
        $this->folderTranslations = new ArrayCollection();
        $this->documentTranslations = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * Return available locales in an array.
     *
     * @return array
     * @SymfonySerializer\Ignore
     */
    public static function getAvailableLocales(): array
    {
        return array_keys(static::$availableLocales);
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getOneLineSummary(): string
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId() . " — " . $this->getName() . " (" . $this->getLocale() . ')' .
        " — " . ($this->isAvailable() ? 'Enabled' : 'Disabled') .
        ($this->isDefaultTranslation() ? ' - Default' : '') .  PHP_EOL;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): Translation
    {
        $this->name = $name ?? '';
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale): Translation
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param boolean $available
     *
     * @return $this
     */
    public function setAvailable(bool $available): Translation
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDefaultTranslation(): bool
    {
        return $this->defaultTranslation;
    }

    /**
     * @param boolean $defaultTranslation
     *
     * @return $this
     */
    public function setDefaultTranslation(bool $defaultTranslation): Translation
    {
        $this->defaultTranslation = $defaultTranslation;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNodeSources(): Collection
    {
        return $this->nodeSources;
    }

    /**
     * @return Collection
     */
    public function getTagTranslations(): Collection
    {
        return $this->tagTranslations;
    }

    /**
     * @return Collection
     */
    public function getDocumentTranslations(): Collection
    {
        return $this->documentTranslations;
    }

    /**
     * Gets the value of overrideLocale.
     *
     * @return string
     */
    public function getOverrideLocale(): ?string
    {
        return $this->overrideLocale;
    }

    /**
     * Sets the value of overrideLocale.
     *
     * @param string|null $overrideLocale the override locale
     *
     * @return self
     */
    public function setOverrideLocale(?string $overrideLocale): Translation
    {
        $this->overrideLocale = StringHandler::slugify($overrideLocale);

        return $this;
    }

    /**
     * Get preferred locale between overrideLocale or locale.
     *
     * @return string
     */
    public function getPreferredLocale(): string
    {
        return !empty($this->overrideLocale) ? $this->overrideLocale : $this->locale;
    }

    /**
     * @return bool
     */
    public function isRtl(): bool
    {
        return in_array($this->getLocale(), static::getRightToLeftLocales());
    }

    /**
     * @return array
     * @SymfonySerializer\Ignore
     */
    public static function getRightToLeftLocales(): array
    {
        return array_keys(static::$rtlLanguages);
    }

    /**
     * @return Collection
     * @SymfonySerializer\Ignore
     */
    public function getFolderTranslations(): Collection
    {
        return $this->folderTranslations;
    }
}
