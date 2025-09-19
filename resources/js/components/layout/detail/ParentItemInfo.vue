<template>
  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <Title variant="system" description="Parent item information that this detail belongs to.">
        Parent Item
      </Title>
    </div>
    <div :class="['border-t', getThemeClass('dropdownBorder')]">
      <DescriptionList>
        <DescriptionRow variant="gray" size="small">
          <DescriptionTerm variant="small-gray">Item ID</DescriptionTerm>
          <DescriptionDetail variant="small-gray">
            <UuidDisplay :uuid="itemId" format="long" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white" size="small">
          <DescriptionTerm variant="small-gray">Internal Name</DescriptionTerm>
          <DescriptionDetail variant="small-gray">
            <InternalName :internal-name="itemInternalName" small />
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </div>
  </div>
</template>

<script setup lang="ts">
  import UuidDisplay from '@/components/format/Uuid.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import Title from '@/components/format/title/Title.vue'
  import { getThemeClass } from '@/composables/useColors'

  defineProps<{
    itemId: string
    itemInternalName: string
  }>()
</script>