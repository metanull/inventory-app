```instructions
---
applyTo: "resources/js/views/__tests__/**/*.test.ts"
---
- **CRITICAL:** Organize tests in a logical and consistent directory structure.
- **CRITICAL:** Keep tests simple and focused on a single behavior.
- **CRITICAL:** Keep tests isolated and independent from each other.
- **CRITICAL:** Use meaningful test names that describe the behavior being tested.
- **CRITICAL:** Always preserve consistancy with existing code. 
- **CRITICAL:** Check how things are done in other source files and also check the actual pinia store implementation and vue implementation when necessary. 
- **CRITICAL:** Always preserve consistancy in the way mocking and import is performed for tests.
- **CRITICAL:** Always preserve consistancy in the way tests files are named, structured and organized.

## Test Organization Structure

Tests are organized into 5 specialized directories:

### Feature Tests (`feature/`)
Main component functionality and behavior tests:
    - `resources/js/views/__tests__/feature/ProjectDetail.test.ts`
    - `resources/js/views/__tests__/feature/Projects.test.ts`

### Integration Tests (`integration/`)  
Component integration and workflow tests:
    - `resources/js/views/__tests__/integration/ProjectDetail.test.ts`
    - `resources/js/views/__tests__/integration/Project.test.ts`

### Logic Tests (`logic/`)
Business logic and computational tests:
    - `resources/js/views/__tests__/logic/ProjectDetail.test.ts`
    - `resources/js/views/__tests__/logic/Projects.test.ts`

### Resource Integration Tests (`resource_integration/`)
API resource integration tests with `.tests.ts` suffix:
    - `resources/js/views/__tests__/resource_integration/ProjectDetailResource.tests.ts`
    - `resources/js/views/__tests__/resource_integration/ProjectResource.tests.ts`

### Consistency Tests (`consistency/`)
Cross-entity consistency validation tests:
    - `resources/js/views/__tests__/consistency/Project.test.ts` 
    
**CRITICAL:** Consistency test files are expected to be strictly copied across entities. Their purpose is to assert that all features are consistent compared to others. It is vital that the consistency tests are identical for all copies.