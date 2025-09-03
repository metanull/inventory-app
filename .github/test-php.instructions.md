---
applyTo: "\tests\**\*Test.php"
---
**CRITICAL: Organize tests in a logical and consistent directory structure.**
**CRITICAL: Keep tests simple and focused on a single behavior.**
**CRITICAL: Keep tests isolated and independent from each other.**
**CRITICAL: Use meaningful test names that describe the behavior being tested.**
**CRITICAL: In test, never assume existence of a record, create records using factories and use the `refreshDatabase` trait.**
**CRITICAL: Use existing tests as a reference for creating new tests: [tests/Feature/Api/Language/AnonymousTest.php], [tests/Feature/Api/Language/DestroyTest.php], [tests/Feature/Api/Language/IndexTest.php], [tests/Feature/Api/Language/ShowTest.php], [tests/Feature/Api/Language/StoreTest.php], [tests/Feature/Api/Language/UpdateTest.php], [tests/Feature/Event/AvailableImage/AvailableImageTest.php].**