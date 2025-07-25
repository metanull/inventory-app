<template>
  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <Title variant="system" description="Internal system data managed by the API.">
        System Properties
      </Title>
    </div>
    <div class="border-t border-gray-200">
      <DescriptionList>
        <DescriptionRow variant="gray" size="small">
          <DescriptionTerm variant="small-gray">ID</DescriptionTerm>
          <DescriptionDetail variant="small-gray">
            <UuidDisplay :uuid="id" format="long" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white" size="small">
          <DescriptionTerm variant="small-gray">Created</DescriptionTerm>
          <DescriptionDetail variant="small-gray">
            <DateDisplay :date="createdAt" format="medium" show-time />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray" size="small">
          <DescriptionTerm variant="small-gray">Last Updated</DescriptionTerm>
          <DescriptionDetail variant="small-gray">
            <DateDisplay :date="updatedAt" format="medium" show-time />
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </div>
  </div>
</template>

<script setup lang="ts">
  import DateDisplay from '@/components/format/Date.vue'
  import UuidDisplay from '@/components/format/Uuid.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import Title from '@/components/format/title/Title.vue'

  defineProps<{
    id: string
    createdAt: string | null
    updatedAt: string | null
  }>()
</script>
