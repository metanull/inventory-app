import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PaginationControls from '@/components/layout/list/PaginationControls.vue'

describe('PaginationControls.vue', () => {
  it('renders current page and total', () => {
    const wrapper = mount(PaginationControls, {
      props: { page: 2, perPage: 20, total: 95, color: 'blue' },
    })
    expect(wrapper.text()).toContain('Showing 21–40 of 95')
    expect(wrapper.text()).toContain('Page 2 / 5')
  })

  it('emits update:page on next/previous clicks', async () => {
    const wrapper = mount(PaginationControls, {
      props: { page: 2, perPage: 10, total: 30 },
    })
    const buttons = wrapper.findAll('button')
    // buttons: [Prev, Page x / y (disabled), Next]
    await buttons[0].trigger('click')
    await buttons[2].trigger('click')
    const emits = wrapper.emitted('update:page')
    expect(emits).toBeTruthy()
    expect(emits?.[0]).toEqual([1])
    expect(emits?.[1]).toEqual([3])
  })

  it('disables prev on first page and next on last page', async () => {
    const first = mount(PaginationControls, { props: { page: 1, perPage: 10, total: 5 } })
    const firstButtons = first.findAll('button')
    expect(firstButtons[0].attributes('disabled')).toBeDefined()

    const last = mount(PaginationControls, { props: { page: 3, perPage: 10, total: 25 } })
    const lastButtons = last.findAll('button')
    expect(lastButtons[2].attributes('disabled')).toBeDefined()
  })

  it('emits update:per-page when changing page size', async () => {
    const wrapper = mount(PaginationControls, { props: { page: 1, perPage: 20, total: 100 } })
    const select = wrapper.find('select')
    await select.setValue('50')
    const emits = wrapper.emitted('update:per-page')
    expect(emits).toBeTruthy()
    expect(emits?.[0]).toEqual([50])
  })
})
