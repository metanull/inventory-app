import { describe, it, expect } from 'vitest'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

describe('Item Consistency Tests', () => {
  const itemListPath = path.resolve(__dirname, '../../Items.vue')
  const itemDetailPath = path.resolve(__dirname, '../../ItemDetail.vue')

  describe('Items.vue - List View Compliance', () => {
    it('should use ErrorDisplay store for error handling', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('useErrorDisplayStore')
      expect(content).toContain("import { useErrorDisplayStore } from '@/stores/errorDisplay'")
    })

    it('should use LoadingOverlay store for loading states', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('useLoadingOverlayStore')
      expect(content).toContain("import { useLoadingOverlayStore } from '@/stores/loadingOverlay'")
    })

    it('should use only Heroicons and no hardcoded SVGs', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('@heroicons/vue')

      // Check for hardcoded SVG - should not find any <svg> tags in template
      const templateMatch = content.match(/<template[^>]*>([\s\S]*?)<\/template>/i)
      if (templateMatch) {
        const templateContent = templateMatch[1]
        expect(templateContent).not.toMatch(/<svg[\s\S]*?<\/svg>/i)
      }
    })

    it('should have ViewButton for list items', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('ViewButton')
      expect(content).toContain("import ViewButton from '@/components/layout/list/ViewButton.vue'")
    })

    it('should use ListView component for consistent layout', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('ListView')
      expect(content).toContain("import ListView from '@/components/layout/list/ListView.vue'")
    })

    it('should use proper table structure components', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('TableCell')
      expect(content).toContain('TableRow')
      expect(content).toContain('TableHeader')
      expect(content).toContain(
        "import TableHeader from '@/components/format/table/TableHeader.vue'"
      )
      expect(content).toContain("import TableRow from '@/components/format/table/TableRow.vue'")
      expect(content).toContain("import TableCell from '@/components/format/table/TableCell.vue'")
    })

    it('should use format components instead of raw data in table cells', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')

      // Should use DateDisplay for dates
      expect(content).toContain('DateDisplay')
      expect(content).toContain("import DateDisplay from '@/components/format/Date.vue'")

      // Should use InternalName component for names
      expect(content).toContain('InternalName')

      // Should not have direct field access in template without format components
      // Check that dates are formatted with DateDisplay, not raw
      expect(content).toContain('<DateDisplay :date="item.created_at"')
    })

    it('should have proper action buttons in table rows', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      expect(content).toContain('ViewButton')
      expect(content).toContain('EditButton')
      expect(content).toContain('DeleteButton')
      expect(content).toContain("import ViewButton from '@/components/layout/list/ViewButton.vue'")
      expect(content).toContain("import EditButton from '@/components/layout/list/EditButton.vue'")
      expect(content).toContain(
        "import DeleteButton from '@/components/layout/list/DeleteButton.vue'"
      )
    })

    it('should use centralized color system consistently', () => {
      const content = fs.readFileSync(itemListPath, 'utf-8')
      
      // Should import centralized color system
      expect(content).toContain("import { useColors, type ColorName } from '@/composables/useColors'")
      
      // Should use ColorName type for props
      expect(content).toContain('color?: ColorName')
      
      // Should use centralized useColors composable
      expect(content).toContain('useColors(computed(() => props.color))')
      
      // Should default to 'teal' for items
      expect(content).toContain("color: 'teal'")
      
      // Should NOT have local colorMap definitions
      expect(content).not.toContain('const colorMap: Record<string,')
    })
  })

  describe('ItemDetail.vue - Detail View Compliance', () => {
    it('should use CancelChangesConfirmation store for unsaved changes protection', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('useCancelChangesConfirmationStore')
      expect(content).toContain(
        "import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'"
      )
    })

    it('should use ErrorDisplay store for error handling', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('useErrorDisplayStore')
      expect(content).toContain("import { useErrorDisplayStore } from '@/stores/errorDisplay'")
    })

    it('should use DeleteConfirmation store for delete operations', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('useDeleteConfirmationStore')
      expect(content).toContain(
        "import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'"
      )
    })

    it('should use LoadingOverlay store for loading states', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('useLoadingOverlayStore')
      expect(content).toContain("import { useLoadingOverlayStore } from '@/stores/loadingOverlay'")
    })

    it('should have proper view/edit/create mode toggling logic', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('enterEditMode')
      expect(content).toContain('enterViewMode')
      expect(content).toContain('enterCreateMode')
      expect(content).toContain("mode.value = 'view'")
      expect(content).toContain("mode.value = 'edit'")
      expect(content).toContain("mode.value = 'create'")
    })

    it('should use only Heroicons and no hardcoded SVGs', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('@heroicons/vue')

      // Check for hardcoded SVG - should not find any <svg> tags in template
      const templateMatch = content.match(/<template[^>]*>([\s\S]*?)<\/template>/i)
      if (templateMatch) {
        const templateContent = templateMatch[1]
        expect(templateContent).not.toMatch(/<svg[\s\S]*?<\/svg>/i)
      }
    })

    it('should use DetailView component for consistent layout', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('DetailView')
      expect(content).toContain(
        "import DetailView from '@/components/layout/detail/DetailView.vue'"
      )
    })

    it('should implement onBeforeRouteLeave navigation guard', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('onBeforeRouteLeave')
      expect(content).toContain('hasUnsavedChanges')
      expect(content).toContain('cancelChangesStore.trigger')
    })

    it('should use proper description list structure for data display', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      expect(content).toContain('DescriptionList')
      expect(content).toContain('DescriptionRow')
      expect(content).toContain('DescriptionTerm')
      expect(content).toContain('DescriptionDetail')
      expect(content).toContain(
        "import DescriptionList from '@/components/format/description/DescriptionList.vue'"
      )
      expect(content).toContain(
        "import DescriptionRow from '@/components/format/description/DescriptionRow.vue'"
      )
      expect(content).toContain(
        "import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'"
      )
      expect(content).toContain(
        "import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'"
      )
    })

    it('should use format components for data display instead of raw values', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should use DisplayText for text values
      expect(content).toContain('DisplayText')
      expect(content).toContain("import DisplayText from '@/components/format/DisplayText.vue'")

      // Should use DateDisplay for dates
      expect(content).toContain('DateDisplay')
      expect(content).toContain("import DateDisplay from '@/components/format/Date.vue'")

      // Should use FormInput for edit mode
      expect(content).toContain('FormInput')
      expect(content).toContain("import FormInput from '@/components/format/FormInput.vue'")
    })
  })

  describe('Navigation and Menu Integration', () => {
    it('should have proper navigation guard pattern', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should have async navigation guard with proper result handling
      expect(content).toContain('async (')
      expect(content).toContain('next: NavigationGuardNext')
      expect(content).toContain("result === 'stay'")
      expect(content).toContain('next(false)')
      expect(content).toContain('cancelChangesStore.resetChanges()')
    })

    it('should have proper cancel action pattern', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should have cancelAction with proper confirmation flow
      expect(content).toContain('const cancelAction = async () => {')
      expect(content).toContain('cancelChangesStore.trigger')
      expect(content).toContain("mode.value === 'create'")
      expect(content).toContain('enterViewMode()')
    })

    it('should have initializeComponent method and use it in onMounted', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should have initializeComponent function
      expect(content).toContain('const initializeComponent = async () => {')

      // Should use it in onMounted
      expect(content).toContain('onMounted(initializeComponent)')

      // Should handle route-based logic for create/edit/view modes
      expect(content).toContain('enterCreateMode()')
      expect(content).toContain('enterEditMode()')
      expect(content).toContain('enterViewMode()')
    })

    it('should use named routes consistently for navigation', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should use named routes instead of string paths
      expect(content).toContain("router.push({ name: 'items' })")

      // Should NOT use string paths for main navigation
      expect(content).not.toContain("router.push('/items')")
      expect(content).not.toContain('router.push("/items")')
    })

    it('should use router.replace for query parameter updates', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')

      // Should use router.replace when updating query parameters
      expect(content).toContain('await router.replace({ query })')

      // Should not use router.push for query parameter updates
      expect(content).not.toContain('router.push({ query })')
    })

    it('should use centralized color system consistently', () => {
      const content = fs.readFileSync(itemDetailPath, 'utf-8')
      
      // Should import centralized color system
      expect(content).toContain("import { useColors, type ColorName } from '@/composables/useColors'")
      
      // Should use ColorName type for props
      expect(content).toContain('color?: ColorName')
      
      // Should use centralized useColors composable
      expect(content).toContain('useColors(computed(() => props.color))')
      
      // Should default to 'teal' for items
      expect(content).toContain("color: 'teal'")
      
      // Should use colorClasses.icon instead of hardcoded colors
      expect(content).toContain('colorClasses.icon')
      
      // Should NOT have local colorMap definitions
      expect(content).not.toContain('const colorMap: Record<string,')
    })
  })
})
