<x-glossary.sub-index 
    :glossary="$glossary"
    :items="$translations"
    item-type="translation"
    item-display-field="definition"
    :create-route="route('glossaries.translations.create', $glossary)"
    show-route-pattern="{{ route('glossaries.translations.show', ['{glossary}', '{item}']) }}"
    :back-route="route('glossaries.show', $glossary)"
/>
