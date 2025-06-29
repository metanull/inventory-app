---
layout: post
title: "Development Summary - Saturday, June 14, 2025"
date: 2025-06-14 12:00:00 +0000
author: Development Team
commit_count: 47
post_type: daily-summary
categories: [daily, development]
tags: [git, summary, development]
github_refs: [75, 76, 78, 79, 80, 81, 86, 87, 88, 90, 91, 92, 93, 94]
---

## Development Activity for June 14, 2025

### Summary

**Total Commits:** 47  
**Contributors:** 9  
**Files Changed:** 49  
**Lines Added:** +2520  
**Lines Removed:** -4096  

### Related Issues & Pull Requests

- [Issue/PR #75](https://github.com/metanull/inventory-app/issues/75)
- [Issue/PR #76](https://github.com/metanull/inventory-app/issues/76)
- [Issue/PR #78](https://github.com/metanull/inventory-app/issues/78)
- [Issue/PR #79](https://github.com/metanull/inventory-app/issues/79)
- [Issue/PR #80](https://github.com/metanull/inventory-app/issues/80)
- [Issue/PR #81](https://github.com/metanull/inventory-app/issues/81)
- [Issue/PR #86](https://github.com/metanull/inventory-app/issues/86)
- [Issue/PR #87](https://github.com/metanull/inventory-app/issues/87)
- [Issue/PR #88](https://github.com/metanull/inventory-app/issues/88)
- [Issue/PR #90](https://github.com/metanull/inventory-app/issues/90)
- [Issue/PR #91](https://github.com/metanull/inventory-app/issues/91)
- [Issue/PR #92](https://github.com/metanull/inventory-app/issues/92)
- [Issue/PR #93](https://github.com/metanull/inventory-app/issues/93)
- [Issue/PR #94](https://github.com/metanull/inventory-app/issues/94)

### Direct Commits


- **cc8fea8**: - Enable/fix testing setup - Configure env.testing to use a distinct database   - user *must* call  `php artisan migrate --env=testing` before running `php artisan test` - Adding a basic test case fot eh Country resource endpoint ([view](https://github.com/metanull/inventory-app/commit/cc8fea83e3c216fb7e69cbe9b1eae575a442a99b)) - *by Pascal HAVELANGE*
- **72967b5**: Fix issue with migration 2025-06-08 ([view](https://github.com/metanull/inventory-app/commit/72967b5a206cab6df83b5103f5c39361c88311f3)) - *by Pascal HAVELANGE*
- **d6a9803**: Issues with Factories, found an idea, putting it as a comment ([view](https://github.com/metanull/inventory-app/commit/d6a9803c26fcf18c54448d14f18ce959d5b2678f)) - *by Pascal HAVELANGE*
- **c748957**: Linting ([view](https://github.com/metanull/inventory-app/commit/c74895755d08173687606f968aa327efffb292eb)) - *by Pascal HAVELANGE*
- **da2677e**: Update tests/TestCase.php ([view](https://github.com/metanull/inventory-app/commit/da2677ec81792604b0443d903237d75b8c410ded)) - *by PascalHavelange*
- **2b50cbc**: Update .env.testing ([view](https://github.com/metanull/inventory-app/commit/2b50cbc0508811ae7e3985d29523300a50a7096f)) - *by PascalHavelange*
- **79e4b98**: Enabling testing (#75) ([view](https://github.com/metanull/inventory-app/commit/79e4b9870235d6ce2475bb07e7638573dbd13a7c)) - *by PascalHavelange*
- **7fad1db**: Merge branch 'main' into develop ([view](https://github.com/metanull/inventory-app/commit/7fad1db5d2c62312233910dcc8e8da148cf9d5bf)) - *by Pascal HAVELANGE*
- **338e778**: Removing Artisan facade from TestCase class,  as it is not used anymore ([view](https://github.com/metanull/inventory-app/commit/338e7781b040722ebe68d0eeeac6b05b287ea2cf)) - *by Pascal HAVELANGE*
- **d339af6**: Removing Artisan facade from TestCase class,  as it is not used anymore (#76) ([view](https://github.com/metanull/inventory-app/commit/d339af66bb388239e8084f4168593e0403461ea5)) - *by PascalHavelange*
- **530a952**: Create laravel.yml - Windows-latest action to run artisan test ([view](https://github.com/metanull/inventory-app/commit/530a95222ffd13695e43f7c82526c8482dfa7b48)) - *by PascalHavelange*
- **693fc9f**: Remove phpcs, replace by laravel's Pint Lint ([view](https://github.com/metanull/inventory-app/commit/693fc9f81a154359128dcad03679e7053a293eca)) - *by Pascal HAVELANGE*
- **efc1be3**: Change php version to 8.1 in Pint github action, to fix composer error "Your lock file does not contain a compatible set of packages" ([view](https://github.com/metanull/inventory-app/commit/efc1be32597c16ee2de8ce8179376b7a1d2e8cc6)) - *by Pascal HAVELANGE*
- **d414ee6**: Running composer update in the pipeline, to attempt fixing "Your lock file does not contain a compatible set of packages" ([view](https://github.com/metanull/inventory-app/commit/d414ee6a8d7c840234f8a824afd1b56e59728683)) - *by Pascal HAVELANGE*
- **fb612b8**: - Let setup-php pick the required php version from composer.lock - Add fileinfo, zip and xdebug extensions ([view](https://github.com/metanull/inventory-app/commit/fb612b841a8263800b46c3c8859d383aaba06610)) - *by Pascal HAVELANGE*
- **8cdfb9d**: Yaml: add step to generate .env from (he 'testing' template ([view](https://github.com/metanull/inventory-app/commit/8cdfb9d79b2d3b7cf1b85ca8cd30bd0eb3542cb9)) - *by Pascal HAVELANGE*
- **a10dda3**: Yaml, Adding  sqlite extension to php ([view](https://github.com/metanull/inventory-app/commit/a10dda34de28fdefefa330744f9e9fb8c6891891)) - *by Pascal HAVELANGE*
- **450547b**: Yaml, making composer "quiet" ([view](https://github.com/metanull/inventory-app/commit/450547b81b1f57dbd38c69c12893d549cd40f146)) - *by Pascal HAVELANGE*
- **0769e98**: Create laravel.yml - Windows-latest action to run artisan test (#78) ([view](https://github.com/metanull/inventory-app/commit/0769e98acbae673fecf4a868652ec8c97820a8e7)) - *by PascalHavelange*
- **2c7cecb**: Adding github actions for dependabot ([view](https://github.com/metanull/inventory-app/commit/2c7cecbc5bec773cf91fbbabb71f1e461f3ff0c0)) - *by Pascal HAVELANGE*
- **d79f167**: Adding github actions for dependabot (#79) ([view](https://github.com/metanull/inventory-app/commit/d79f16736fd22e040bebe3db909a29a510fc687d)) - *by PascalHavelange*
- **4927fc6**: Merge branch 'main' into develop ([view](https://github.com/metanull/inventory-app/commit/4927fc6a1675b43dcfd8e1e3eeafdafbba1155bc)) - *by Pascal HAVELANGE*
- **f26d6b5**: Moving dependabot.yml to .github instead of .github/workflows ([view](https://github.com/metanull/inventory-app/commit/f26d6b54fc953c201620bd55637eb98d21cb83b3)) - *by Pascal HAVELANGE*
- **d912594**: Develop (#80) ([view](https://github.com/metanull/inventory-app/commit/d912594771fe9b7585fb0e934d559694028c584d)) - *by PascalHavelange*
- **e1e708a**: Bump dependabot/fetch-metadata from 1.3.3 to 2.4.0 ([view](https://github.com/metanull/inventory-app/commit/e1e708aeb2d50ef25358fddfb6ae85f27bb73534)) - *by dependabot[bot]*
- **62694f8**: Bump intervention/image from 3.11.2 to 3.11.3 ([view](https://github.com/metanull/inventory-app/commit/62694f85111ae17d781725a7d94788474bb387b0)) - *by dependabot[bot]*
- **e81f19c**: Bump autoprefixer from 10.4.20 to 10.4.21 ([view](https://github.com/metanull/inventory-app/commit/e81f19ce8d9c92c73d451a9b0ae107ee11d7ad69)) - *by dependabot[bot]*
- **ef17e86**: Bump postcss from 8.5.3 to 8.5.5 ([view](https://github.com/metanull/inventory-app/commit/ef17e8697634f3c68831a9381434e4cdec85d2b5)) - *by dependabot[bot]*
- **23aa1c5**: Bump laravel/framework from 11.44.2 to 12.18.0 ([view](https://github.com/metanull/inventory-app/commit/23aa1c50e26faf64036b55f7d20409805a21d8f3)) - *by dependabot[bot]*
- **8a34838**: Bump pestphp/pest from 3.8.0 to 3.8.2 ([view](https://github.com/metanull/inventory-app/commit/8a34838ac1f324fda5fa71e49db778cccf90cec9)) - *by dependabot[bot]*
- **a4a2265**: Bump dependabot/fetch-metadata from 1.3.3 to 2.4.0 (#81) ([view](https://github.com/metanull/inventory-app/commit/a4a2265e026a7fe719853a71e695470e1362f91f)) - *by PascalHavelange*
- **c359059**: Merge branch 'main' into dependabot/composer/pestphp/pest-3.8.2 ([view](https://github.com/metanull/inventory-app/commit/c359059ba4a4118137e915f47560f697ec3a4bc3)) - *by PascalHavelange*
- **ec88ba7**: Bump pestphp/pest from 3.8.0 to 3.8.2 (#91) ([view](https://github.com/metanull/inventory-app/commit/ec88ba7caba670c6d8560672cbe6321464136cda)) - *by PascalHavelange*
- **f72c4c3**: Merge branch 'main' into dependabot/npm_and_yarn/postcss-8.5.5 ([view](https://github.com/metanull/inventory-app/commit/f72c4c3668451a303aef8feabb710fffac5bde30)) - *by PascalHavelange*
- **a9a9cff**: Bump postcss from 8.5.3 to 8.5.5 (#88) ([view](https://github.com/metanull/inventory-app/commit/a9a9cff4b904d021255c620169951243d0ae95fc)) - *by PascalHavelange*
- **0cc5dbd**: Bump laravel/framework from 11.44.2 to 12.18.0 (#90) ([view](https://github.com/metanull/inventory-app/commit/0cc5dbd2103712eda2a1c276ad8767094011919b)) - *by PascalHavelange*
- **ef1ecbe**: Bump intervention/image from 3.11.2 to 3.11.3 (#87) ([view](https://github.com/metanull/inventory-app/commit/ef1ecbef85fc38e6b72413e350510982666cb5e4)) - *by PascalHavelange*
- **133b31e**: Bump autoprefixer from 10.4.20 to 10.4.21 (#86) ([view](https://github.com/metanull/inventory-app/commit/133b31ec0c090f7b25c8f4b0c3365e99f6f357d7)) - *by PascalHavelange*
- **a15f29b**: bumping composer and npm dependencies ([view](https://github.com/metanull/inventory-app/commit/a15f29b4aaa7ef5dc54b4dea2a906ac84db926a8)) - *by Pascal HAVELANGE*
- **1dbacd4**: bumping composer and npm dependencies (#92) ([view](https://github.com/metanull/inventory-app/commit/1dbacd4c917ccbb5fe26dbfb3a0aed5385b985aa)) - *by PascalHavelange*
- **f37bc84**: Enable Pint --bail to block the pipeline in case of linting errors ([view](https://github.com/metanull/inventory-app/commit/f37bc84b339b742ef006ca9a3baccffc90f9f187)) - *by Pascal HAVELANGE*
- **56ca29e**: Enable Pint --bail to block the pipeline in case of linting errors (#93) ([view](https://github.com/metanull/inventory-app/commit/56ca29e7fe2d4f9803230336995f8b597151fc13)) - *by PascalHavelange*
- **1f473ed**: Potential fix for code scanning alert no. 1: Workflow does not contain permissions ([view](https://github.com/metanull/inventory-app/commit/1f473ed0efe6b383e01c2d1841a15e07bf47bc68)) - *by PascalHavelange*
- **9e2046b**: Potential fix for code scanning alert no. 1: Workflow does not contain permissions (#94) ([view](https://github.com/metanull/inventory-app/commit/9e2046bcfeb3572580406fe0a44f45d5970c6eba)) - *by PascalHavelange*
- **a2c1a2b**: - Adding tests for "Language" API and "Country" API - Adding Commands: pint:test, pint:repair and pint:bail - Adding Command lint (which invokes pint:repair) ([view](https://github.com/metanull/inventory-app/commit/a2c1a2bf73a1267b1c617384bf534fb01e82c1e3)) - *by Pascal HAVELANGE*
- **3a61451**: Update database/factories/LanguageFactory.php ([view](https://github.com/metanull/inventory-app/commit/3a61451a361e96ed6e1e92f42d34143bec554acb)) - *by PascalHavelange*
- **00b344e**: Update database/factories/CountryFactory.php ([view](https://github.com/metanull/inventory-app/commit/00b344e352b66b200ff49e34a3e81508d67260fb)) - *by PascalHavelange*

### Contributors

- **Pascal** (0
0 commit)
- **HAVELANGE** (0
0 commit)
- **PascalHavelange** (22 commits)
- **dependabot[bot]** (0
0 commit)
- **dependabot[bot]** (0
0 commit)
- **dependabot[bot]** (0
0 commit)
- **dependabot[bot]** (0
0 commit)
- **dependabot[bot]** (0
0 commit)
- **dependabot[bot]** (0
0 commit)

---

*This daily summary was automatically generated from 47 commit(s) made on June 14, 2025.*
