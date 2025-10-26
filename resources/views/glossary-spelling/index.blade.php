<x-glossary.sub-index 
    :glossary="$glossary"
    :items="$spellings"
    item-type="spelling"
    item-display-field="spelling"
    :create-route="route('glossaries.spellings.create', $glossary)"
    show-route-pattern="{{ route('glossaries.spellings.show', ['{glossary}', '{item}']) }}"
    :back-route="route('glossaries.show', $glossary)"
/>
