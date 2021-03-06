## 2.0.13 (2022-07-29)

### Bug Fixes

* Remove useless eager join on document downscaledDocuments on DocumentRepository ([d821586](https://github.com/roadiz/core-bundle/commit/d82158616c6e8259c9264715e458bf1e2f0ccdb7))

## 2.0.12 (2022-07-29)

### Bug Fixes

* **Serializer:** Ignore can only be added on methods beginning with "get", "is", "has" or "set" ([78b52aa](https://github.com/roadiz/core-bundle/commit/78b52aa794413b73f67b08efad787300f6ebf07a))

## 2.0.11 (2022-07-29)

### Features

* Added `altSources` to Document DTO and optimize document downscaled relationship ([82a5fd6](https://github.com/roadiz/core-bundle/commit/82a5fd6cd0e37f15bff81655d34f63f9b2897fb3))

## 2.0.10 (2022-07-29)

### Bug Fixes

* DocumentFinder now extends AbstractDocumentFinder ([670516a](https://github.com/roadiz/core-bundle/commit/670516a9fbbdb7d312c356acc7f5626059f2150d))

## 2.0.9 (2022-07-25)

### Bug Fixes

* **SearchEngine:** Do no trigger error on Solr messages if Solr is not available ([785c559](https://github.com/roadiz/core-bundle/commit/785c5593db7a0fa4a3b11e3d277a035ff63d2361))

## 2.0.8 (2022-07-21)

### Bug Fixes

* Do not index empty arrays since [solariumphp/solarium 6.2.5](https://github.com/solariumphp/solarium/issues/1023) breaks empty array indexing ([c9da177](https://github.com/roadiz/core-bundle/commit/c9da177fd9af28e273048373f45c846ec8ca75d7))

## 2.0.7 (2022-07-20)

### Features

* Added new `NodeTranslator` service and remove dead code on User entity ([7f211c5](https://github.com/roadiz/core-bundle/commit/7f211c5354dac0ec953138a514e5d4e82f06e41f))

## 2.0.6 (2022-07-20)

### Bug Fixes

* Attach documents to custom-form notification emails ([c213e87](https://github.com/roadiz/core-bundle/commit/c213e87f9095ac1e21bda17c08cf7d5f389dff7b))

## 2.0.5 (2022-07-13)

### Features

* Added `NotFilter` ([29a608d](https://github.com/roadiz/core-bundle/commit/29a608d76782a68ddfa2e25b7e4ab5e8081cd3e2))
* Purge custom-form answers **documents** as well when retention time is over. ([a00a619](https://github.com/roadiz/core-bundle/commit/a00a619b5458c443f5099e59dfa964518d49e88d))

## 2.0.4 (2022-07-11)

### ??? BREAKING CHANGES

* WebResponseInterface now requires `getItem(): ?PersistableInterface` method to be implemented.

### Bug Fixes

* Set context translation from a WebResponseInterface object ([fbde288](https://github.com/roadiz/core-bundle/commit/fbde288f157f6c2bd84aadb786a8f23ed73300c2))

## 2.0.3 (2022-07-06)

### Bug Fixes

* Mailer test command sender and origin emails ([ae26d01](https://github.com/roadiz/core-bundle/commit/ae26d014fcf62c878f3c9e08c260313b9d855752))

## 2.0.2 (2022-07-05)

### Features

Added true filtrable archives endpoint extension for any Doctrine entities ([597803d](https://github.com/roadiz/core-bundle/commit/597803d37cb324c3d7076f323a5821d497e9fbf5)).
You need to add a custom collection operation for each Entity you want to enable archives for:

```yaml
# config/api_resources/nodes_sources.yml
RZ\Roadiz\CoreBundle\Entity\NodesSources:
    iri: NodesSources
    shortName: NodesSources
    collectionOperations:
        # ...
        archives:
            method: 'GET'
            path: '/nodes_sources/archives'
            pagination_enabled: false
            pagination_client_enabled: false
            archive_enabled: true
            archive_publication_field_name: publishedAt
            normalization_context:
                groups:
                    - get
                    - archives
            openapi_context:
                summary: Get available NodesSources archives
                parameters: ~
                description: |
                    Get available NodesSources archives (years and months) based on their `publishedAt` field
```


## 2.0.1 (2022-07-05)

### Features

* Added `IntersectionFilter` to create intersection with tags and folders aware entities. ([25c1dc5](https://github.com/roadiz/core-bundle/commit/25c1dc54b46dfadc02ed17b8e9de892eed784d73))

## 2.0.0 (2022-07-01)

### ??? BREAKING CHANGES

* `LoginRequestTrait` using Controller must implement getUserViewer() method.
* You must now define getByPath itemOperation for each routable API resource.
* Solr handler must be used with SolrSearchResults (for results and count)
* Rename @Rozier to @RoadizRoadiz

### Features

* Added Realm, RealmNode types, events and async messenging logic to propagate realms relationships in node-tree. ([c53cbec](https://github.com/roadiz/core-bundle/commit/c53cbec87f03178ed7e9f9ea8969426ab332ed33))
* Accept Address object as receiver ([4f5f925](https://github.com/roadiz/core-bundle/commit/4f5f925cf50a9e66f3be4db0d0e3f605465143c6))
* Add node' position to its DTO ([6dae0d6](https://github.com/roadiz/core-bundle/commit/6dae0d6f53c2bc431315bac294cef5ac1970193d))
* Added `--dry-run` option for documents:files:prune command ([8b61694](https://github.com/roadiz/core-bundle/commit/8b616942dd9967aa26a2e1844fc544edbfd09fcf))
* Added CircularReferenceHandler to expose only object ID ([8c9ddbd](https://github.com/roadiz/core-bundle/commit/8c9ddbd89210b9c88a1d9c7af3ff03d5fd8706d8))
* Added custom-form retention time ([22383e9](https://github.com/roadiz/core-bundle/commit/22383e91eb140c61dc019447536f2be2e90a0488))
* Added default NodesSources search and archives controller ([b8ff98b](https://github.com/roadiz/core-bundle/commit/b8ff98b4e0048bfec2ec178a0c6d7660ff5c6ccf))
* Added document CLI command to hash files and find duplicates ([d138a2a](https://github.com/roadiz/core-bundle/commit/d138a2ab805494de3e74a8977499603180c636d8))
* Added document Dto width, height and mimeType ([62958a3](https://github.com/roadiz/core-bundle/commit/62958a3091c9c90e15b5353088cbbdb8fa2ff229))
* Added DocumentRepository alterQueryBuilderWithCopyrightLimitations method ([637a0b7](https://github.com/roadiz/core-bundle/commit/637a0b7bbf37a0501dcb81649eee1c9943e89459))
* Added documents file_hash and file_hash_algorithm for duplicate detection. ([4549ada](https://github.com/roadiz/core-bundle/commit/4549ada40dba6d762bf85bf62cf401e265c2d176))
* Added generate:api-resources command ([25fd64c](https://github.com/roadiz/core-bundle/commit/25fd64c322c932b49c9ab1c5575993e338806760))
* Added HealthCheckController and appVersion config ([54bf276](https://github.com/roadiz/core-bundle/commit/54bf276bf3d710e4e3226744b20ce387958227f8))
* Added lexik_jwt_authentication ([bd5826d](https://github.com/roadiz/core-bundle/commit/bd5826d168b7373feb4eebb769fbf0b53d8a5575))
* Added missing Document DTO externalUrl ([cbce6f1](https://github.com/roadiz/core-bundle/commit/cbce6f19e35363438b57d8b67264d3cac5981512))
* Added new Archive filter on datetime fields ([0bae8d3](https://github.com/roadiz/core-bundle/commit/0bae8d3efe562fb81b6da051c11842aeb0c09165))
* Added new Document copyrightValidSince and Until fields to restrict document display. ([40a31c2](https://github.com/roadiz/core-bundle/commit/40a31c2e4ebc8c313ee6433c12c66403a436728e))
* Added new role ROLE_ACCESS_DOCUMENTS_LIMITATIONS ([bc564fd](https://github.com/roadiz/core-bundle/commit/bc564fd8d3e5c8ed853c76354a89ed44f359fdca))
* Added new role: ROLE_ACCESS_CUSTOMFORMS_RETENTION ([b3586c4](https://github.com/roadiz/core-bundle/commit/b3586c4c57fc5869dac226b9fb81e1e0b2cd24fb))
* Added new UserJoinedGroupEvent and UserLeavedGroupEvent events ([e12d6e4](https://github.com/roadiz/core-bundle/commit/e12d6e4be12e89fdab1bf31e64c77c7329d2a2bb))
* Added node-source archive operation logic (without filters) ([994d9bc](https://github.com/roadiz/core-bundle/commit/994d9bc14fe334168f5868dab2ba7d2ecf203bdd))
* Added OpenId authenticator ([5cf4383](https://github.com/roadiz/core-bundle/commit/5cf43836f9a8a95ed97aacf0f3a412169b34f52d))
* Added preview user provider and JwtExtensiont to generate secure jwt for frontend previewing ([76d81c0](https://github.com/roadiz/core-bundle/commit/76d81c0799a3df0c93c45556cc56adedae9bd1d7))
* Added Realm and RealmNode entities for defining security policies inside node tree ([99ad2a5](https://github.com/roadiz/core-bundle/commit/99ad2a53051ca9c96dab9d3e908b5c1ebf0491c8))
* Added Realm Security Voter, Normalizer and WebResponse logic ([f35083e](https://github.com/roadiz/core-bundle/commit/f35083ed2269718be149ec477d0a3178b2ae8a13))
* Added RealmResolverInterface to get Nodes realms and check security ([6fe7a00](https://github.com/roadiz/core-bundle/commit/6fe7a00d21991d70fdbdcc5934c8662cfafed181))
* Added RememberMeBadge ([1cf563c](https://github.com/roadiz/core-bundle/commit/1cf563c22d95f484e7721880141043907c5f5894))
* Added Solr document search `copyrightValid` criteria shortcut ([66f4215](https://github.com/roadiz/core-bundle/commit/66f4215497c45ad319f8bcbbfdd1ccd74ec7c560))
* Added translation in serializer context from _locale ([db0b45b](https://github.com/roadiz/core-bundle/commit/db0b45bfc611947b92d650dc34e8be72fad23ba1))
* Added Translation name to Link header to build locale switch menus ([9785438](https://github.com/roadiz/core-bundle/commit/978543882cb96b3e89063703485d0ff913e9cfa2))
* Added validation constraints and groups directly on User entity ([82e01f3](https://github.com/roadiz/core-bundle/commit/82e01f3e8fca7e3078afbe7a44f4d940b0a8079e))
* Added validators translation messages ([a61b9d8](https://github.com/roadiz/core-bundle/commit/a61b9d80c149540f6567f23bd1471a361eff514c))
* Configured `rezozero/crypto` to use encoded settings ([32f59d6](https://github.com/roadiz/core-bundle/commit/32f59d65064785560d4d9c01857c8e3d9285b3b8))
* ContactFormManager flatten form data with real form labels and prevent g-recaptcha-response to be sent ([53e7c9d](https://github.com/roadiz/core-bundle/commit/53e7c9df23741c0eab71f66811159801149dad65))
* Deprecated LoginAttemptManager in favor of built-in Symfony login throttling ([2d4a10e](https://github.com/roadiz/core-bundle/commit/2d4a10ec97969364ba5e829c650e3321a59d3607))
* Do not index not visible tags and folder into Solr ([d8fc516](https://github.com/roadiz/core-bundle/commit/d8fc5167371941ed44530186abcd49a904d773e1))
* Do not search for a locale if first token exceed 11 chars ([9f614d8](https://github.com/roadiz/core-bundle/commit/9f614d8b1c1f39a78d9dc6095eb4284161f7c979))
* **Document:** Added document-translation external URL and missing DB indexes ([c346f7b](https://github.com/roadiz/core-bundle/commit/c346f7bdddeb670b30997d3884ef7ec1ff987efb))
* **documents:** Added mediaDuration to Document DTO ([41673d6](https://github.com/roadiz/core-bundle/commit/41673d63ecc50dcf5ac76e6d823a8d224421326e))
* Filter and Sort Translations by availability, default and locale ([47605f8](https://github.com/roadiz/core-bundle/commit/47605f8cd4900cc4d50de2ccc6294abb2899510b))
* find email from any contact form compound ([3d55930](https://github.com/roadiz/core-bundle/commit/3d559300dc57acf6b20244f947b4c12fa1d386d3))
* Force API Platform to look for real resource configuration and serialization context ([9ee9f42](https://github.com/roadiz/core-bundle/commit/9ee9f42a098340344a73746585887a011a0b561a))
* FormErrorSerializerInterface and RateLimiters ([31c6ca8](https://github.com/roadiz/core-bundle/commit/31c6ca84c6576abb5240d7db8170cd7d53b2c869))
* Index documents copyright limitations dates ([f6bbf0b](https://github.com/roadiz/core-bundle/commit/f6bbf0b50181bab746d6b80dad9c33d4ea6bfebc))
* Jwt authentication supports LoginAttemptManager ([eee4c08](https://github.com/roadiz/core-bundle/commit/eee4c083d0c0223e02bfbc2e4f4ff4a0c6f0fdc8))
* LoginRequestTrait requires UserViewer ([544cdcc](https://github.com/roadiz/core-bundle/commit/544cdcc5c26a364a5b21d2ef99492a6d9feb0691))
* Made alterQueryBuilderWithAuthorizationChecker method public to use it along with Repository ([c567cc5](https://github.com/roadiz/core-bundle/commit/c567cc5cbf96bc86fc6a724e833fc24edff434c2))
* Made AutoChildrenNodeSourceWalker overridable and cacheable ([226ae1a](https://github.com/roadiz/core-bundle/commit/226ae1a10ac30474e24d8e9bef7f082703b016f3))
* Made WebResponse as an API Resource ([b778647](https://github.com/roadiz/core-bundle/commit/b778647dd6561d74e730746bf478650178936673))
* migrate custom-forms preview to Recaptcha v3 script ([1b1bfb1](https://github.com/roadiz/core-bundle/commit/1b1bfb15198f8ceea6954e2e99b5344417bd6d94))
* Moved all OpenID logic to RoadizRozierBundle as it only supports authentication to backoffice. ([0171cbb](https://github.com/roadiz/core-bundle/commit/0171cbb5492ea52c9292c41d92a5895b071db95c))
* Moved Security/Authentication/OpenIdAuthenticator to roadiz/openid package ([4f4e391](https://github.com/roadiz/core-bundle/commit/4f4e3919cc549fb824036010ec1414a865c0488d))
* New DocumentArchiver util to create ZIP archive from documents ([be8a35f](https://github.com/roadiz/core-bundle/commit/be8a35f86f264932ee32964036a81cd56815ab8c))
* NodesSourcesPathResolver can resolve home faster, and resolve default translation based on Accept-Language HTTP header ([00db41a](https://github.com/roadiz/core-bundle/commit/00db41a3e252f1464dbd99cf417da8628273e31b))
* Nullable discovery openId service ([ff555c3](https://github.com/roadiz/core-bundle/commit/ff555c33734d5b564dc248c105cb277dbde2dfbe))
* Only requires symfony/security-core ([f094ca2](https://github.com/roadiz/core-bundle/commit/f094ca260b0ccf6dc0c17920a697aa09131f04a3))
* Optimize NodesSourcesLinkHeaderEventSubscriber with repository method ([a4e5e37](https://github.com/roadiz/core-bundle/commit/a4e5e37fe389d55a6c695f9d880d85d181452c3c))
* postUrl for custom-form dto transformer ([7b3f00f](https://github.com/roadiz/core-bundle/commit/7b3f00fb7ad2d26f4091c87619cf5d0120d04ad2))
* **redirections:** Use recursive path normalization to normalize redirection to redirection ([4b4b03f](https://github.com/roadiz/core-bundle/commit/4b4b03fd7cec11b3462a63ca026d1710af635bd1))
* Refactored document search handler and removed deprecated ([4cfb5df](https://github.com/roadiz/core-bundle/commit/4cfb5dfe58daf0cdcec08629d532fba844ea93c1))
* refactored Document translation indexing using DocumentTranslationIndexingEvent, and make it document indexing overridable ([2f4126c](https://github.com/roadiz/core-bundle/commit/2f4126c02420426d0d2ebe49292c3f9c9d0214b0))
* Removed hide_roadiz_version parameter from DB to remove useless DB query on assets requests ([b7ad3a7](https://github.com/roadiz/core-bundle/commit/b7ad3a71190abc85ea24498fb92e9a5f69ffd707))
* Rename @Rozier to @RoadizRoadiz ([a5ebc4a](https://github.com/roadiz/core-bundle/commit/a5ebc4a7d1fb532165edca1dd36dbd65461c59da))
* Search existing realm-node root or inheritance when editing a node (moving) ([c718059](https://github.com/roadiz/core-bundle/commit/c71805978c1406ff3fa55e4fc2b22f49b80fa1bd))
* Serialize tag parent in DTO ([c48fb11](https://github.com/roadiz/core-bundle/commit/c48fb11681c44b8cc0a7126c58ce1a065b30a728))
* set real _api_resource_class for GetWebResponseByPathController ([f9c0804](https://github.com/roadiz/core-bundle/commit/f9c080447501baf988f027aa719abd77c4676724))
* Simplify UserLocaleSubscriber ([c79bf80](https://github.com/roadiz/core-bundle/commit/c79bf80d5537a1d10d2cd8e25d9616bc67eb25ee))
* Support exception in Log messages context ([3159a6c](https://github.com/roadiz/core-bundle/commit/3159a6c40aca76ccbb86bacf599157c961958559))
* Support non-available locales if previewing ([72cdf19](https://github.com/roadiz/core-bundle/commit/72cdf19563741fa5ed97f44f2d41a9ade9737f4b))
* Support non-available locales if previewing ([ca1ac63](https://github.com/roadiz/core-bundle/commit/ca1ac631206dcb38cc7ee32f066a45c553c97839))
* UniqueNodeGenerator: If parent has only one translation, use parent translation instead of default one for creating children ([d463d02](https://github.com/roadiz/core-bundle/commit/d463d024a171e245ed06f7b1d2b201dae1bf623e))
* Use a factory to create NodeNamePolicyInterface with settings; ([a5a9b9d](https://github.com/roadiz/core-bundle/commit/a5a9b9d90a49935f2a2f5647c9d75ac5f261707e))
* UserProvider support searching user by username or email ([4e6ad3c](https://github.com/roadiz/core-bundle/commit/4e6ad3c74fa56c4a823fc9c01f222d42bf8ee4dd))


### Bug Fixes

* Accept nullable DocumentOutput relativePath and mimeType ([910bc8f](https://github.com/roadiz/core-bundle/commit/910bc8fb10861eb38eadc3488349894efd56dc05))
* Added Assert annotations on User entity for API platform validation ([7685f8e](https://github.com/roadiz/core-bundle/commit/7685f8e0496a16dc0bd989285afb7b2c2a4b110c))
* Added email default sender name with Site name ([d9842ac](https://github.com/roadiz/core-bundle/commit/d9842ac654fc601690c898435822709a1a258414))
* Added getByPath itemOperation into generate command ([220c291](https://github.com/roadiz/core-bundle/commit/220c291de268902071e82e5a8b26c07f5cc75c1e))
* allow null string in AbstractSolarium::cleanTextContent ([aa418fc](https://github.com/roadiz/core-bundle/commit/aa418fccacc4cb7995badd070a6efb176428210d))
* Cache pools to clear ([0cd36c0](https://github.com/roadiz/core-bundle/commit/0cd36c073f8ef35f89da887c1250db6441464344))
* Casting attribute value to string when number input ([5263140](https://github.com/roadiz/core-bundle/commit/5263140cf9c08f79af32ae6e34f47adc84084df9))
* Change Discovery definition argument ([bed0e1b](https://github.com/roadiz/core-bundle/commit/bed0e1b3db9471203097eb79abf1243649c44692))
* Changed LocaleSubscriber priority ([de920ed](https://github.com/roadiz/core-bundle/commit/de920ed3c7aa6b93b2c931885152171b2ce17f52))
* clear existing cache pool in SchemaUpdater ([62a01af](https://github.com/roadiz/core-bundle/commit/62a01af1162ec4a91b9822d323172a1da44ad528))
* Configuration tree type ([de47a3c](https://github.com/roadiz/core-bundle/commit/de47a3cf4a5b8e698ae24a4883cc33e46feebc39))
* Context getAttribute comparison ([016df90](https://github.com/roadiz/core-bundle/commit/016df902f7710db0f04115639be744a415112298))
* do not set api resource GetWebResponseByPathController, it breaks serialization context ([49ccf2f](https://github.com/roadiz/core-bundle/commit/49ccf2f9f3720360b7e21955cd799b27a59afc71))
* Doctrine batch iterating for Solr indexing ([255699a](https://github.com/roadiz/core-bundle/commit/255699ab0dfb6068d39b58fc178d7b08c7eb28b8))
* Fix emptySolr query syntax ([717458f](https://github.com/roadiz/core-bundle/commit/717458f0538752736cf7fa4aa05fa80a0055e0a1))
* Fix ExplorerItemProviderType using asMultiple instead of special multiple option. ([8fdc3da](https://github.com/roadiz/core-bundle/commit/8fdc3da5019d0cb0cb6b81ee04d4e7f91ec8e513))
* Ignore getDefaultSeo ([e2f1a57](https://github.com/roadiz/core-bundle/commit/e2f1a578d046fa5afe3f81fafe8d46f62c3dbce8))
* Improved Recaptcha fields Contraint and naming ([4121345](https://github.com/roadiz/core-bundle/commit/4121345ca37626b5f091d2894068b4fb4e913d63))
* InversedBy relation, shorter log cleanup duration ([67c3ef0](https://github.com/roadiz/core-bundle/commit/67c3ef024e2305b51c15c63adad75a10bd8f06ee))
* Missing Liform transformers ([0e8cb2b](https://github.com/roadiz/core-bundle/commit/0e8cb2b33bba4d7799cc1bb8b728c4880ec50e6e))
* Missing RedirectionPathResolver to resolve redirections ([eaa99c7](https://github.com/roadiz/core-bundle/commit/eaa99c72a7838a4c59034b82c6be7d13f631fd60))
* missing trans on form error message ([68e38a6](https://github.com/roadiz/core-bundle/commit/68e38a6583c87774538e06dc88492188ea76e773))
* New SolariumDocumentTranslation constructor signature ([7f1f8b5](https://github.com/roadiz/core-bundle/commit/7f1f8b591a9bed2ccce55c5f6159392daab30a7d))
* NodesSourcesDto title must accept nullable string ([bf3dec2](https://github.com/roadiz/core-bundle/commit/bf3dec2487ef7f9e70eba445a85d123a0df38d85))
* non-existent cache.doctrine.orm.default.result cache pool ([e5bd921](https://github.com/roadiz/core-bundle/commit/e5bd9217f92dff7473552fbbd95146e16d0535a8))
* Nullable and strict typing AttributeValueTranslation ([4e4bb0a](https://github.com/roadiz/core-bundle/commit/4e4bb0a29b7c8146cc821698515d57b344829bd5))
* nullable custom-form email ([4fbeb58](https://github.com/roadiz/core-bundle/commit/4fbeb5844776e05d133dcd1bf0680401fee73056))
* Nullable roadiz_core.solr.client service reference ([2592084](https://github.com/roadiz/core-bundle/commit/2592084fc4cf79c8ff9942ceea55face1d8999ed))
* Only provide Link header for available translations or previewing user ([3255f58](https://github.com/roadiz/core-bundle/commit/3255f5896f582dd0a496de579fead2522c6e6633))
* OpenIdJwtConfigurationFactory configuration ([800b97b](https://github.com/roadiz/core-bundle/commit/800b97b291b22ff8fcc8763a709131420f451626))
* Prevent hashing non-existing document file ([5e92858](https://github.com/roadiz/core-bundle/commit/5e92858bb1fcdec60cd2ffeb0b1e18f2eaa97185))
* Remove NodeType repository class as well during deletion ([9c4c49e](https://github.com/roadiz/core-bundle/commit/9c4c49e1c5da2a056a9b7ac42819c94f2db3758a))
* Removed method dependency on UrlGeneratorInterface ([cbe9675](https://github.com/roadiz/core-bundle/commit/cbe9675ac20b59df2e0fd2595064a542e45468ef))
* removed optional cache-pools from SchemaUpdater ([ff2de22](https://github.com/roadiz/core-bundle/commit/ff2de227976e81f7abb5453f9e7ab8a607804303))
* Removed request option from Recaptcha constraint and using Form classes ([60a04d4](https://github.com/roadiz/core-bundle/commit/60a04d4e6582233cf08fac0dfd5f4c52b5f4c8eb))
* Rewritten node transtyper ([c1309dd](https://github.com/roadiz/core-bundle/commit/c1309ddbbdffbbf7b4613e33f41d5db1a35b1435))
* Roadiz LocaleSubscriber must be aware of _locale in query parameters too. ([65fe081](https://github.com/roadiz/core-bundle/commit/65fe0811cbefb2e0730ba6a4f7541c53ddd0b4bd))
* SolrDeleteMessage handler ([fefe729](https://github.com/roadiz/core-bundle/commit/fefe729a73684a816d9feffc0eac79c26c52af98))
* support ld+json exception ([fdce0d1](https://github.com/roadiz/core-bundle/commit/fdce0d102b82026ec9051723ea2168f720613cac))
* transactional email style for button ([c588994](https://github.com/roadiz/core-bundle/commit/c5889946d20b1fd583c7f7543fc65c8237f1429d))
* TranslationSubscriber dispatch CachePurgeRequestEvent ([4099304](https://github.com/roadiz/core-bundle/commit/409930477f91798b787d167ac0fd9b610d1373be))
* Uniformize custom-form error JSON response with other form-error responses ([a817fa4](https://github.com/roadiz/core-bundle/commit/a817fa4eeaf1eb7899fe27ad25e17566621bf508))
* unnecessary nullable ObjectManager ([218bbd1](https://github.com/roadiz/core-bundle/commit/218bbd15a4e236c20141eabb776aa84028e2d6b2))
* Update tag and document timestamps when translations were edited ([49774d8](https://github.com/roadiz/core-bundle/commit/49774d8deaa85d46103f8cfccc1f6fcb66a28c23))
* Use empty form name for custom-form ([202ea2a](https://github.com/roadiz/core-bundle/commit/202ea2a63c695ebc7a5d9a0432dc57852af09ce2))
* Use JSON status prop for status code, not message ([2a4f498](https://github.com/roadiz/core-bundle/commit/2a4f498aab74313b07f53ffbdc4ecb0db2adcd4b))
* Use Paginator to avoid pagination issues during Solr node indexing ([f43cae9](https://github.com/roadiz/core-bundle/commit/f43cae9d3ffab43a5a3fa70d1f3bdc29f8b18315))
* Use PriorityTaggedServiceTrait trait to respect tagged service priority ([4e31229](https://github.com/roadiz/core-bundle/commit/4e31229b8c84285fdc445050aafd2e7c7d6306c6))
* Use `rezozero/liform` fork ([863893c](https://github.com/roadiz/core-bundle/commit/863893c8c816289fcee7547d82f35eff8cd4fbeb))
* Use Security to create Preview user when isGranted ([133c6f8](https://github.com/roadiz/core-bundle/commit/133c6f889edb0f8d1ad9216bb54bfb6cb7560512))
* Use single_text widget for Date and DateTime customForm fields ([5bfa1d3](https://github.com/roadiz/core-bundle/commit/5bfa1d3bbc7a04e629fc2c63252f11a68d313dcc))
* User implements getUserIdentifier ([0355b1c](https://github.com/roadiz/core-bundle/commit/0355b1cf44911408d2467582dc64552d2d6ac3b4))

