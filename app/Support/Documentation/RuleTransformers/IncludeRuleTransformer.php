<?php

namespace App\Support\Documentation\RuleTransformers;

use App\Rules\IncludeRule;
use Dedoc\Scramble\Contracts\RuleTransformer;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type;
use Dedoc\Scramble\Support\RuleTransforming\NormalizedRule;
use Dedoc\Scramble\Support\RuleTransforming\RuleTransformerContext;

/**
 * Scramble rule transformer for the IncludeRule validation rule.
 *
 * This transformer generates OpenAPI documentation for the include parameter,
 * automatically extracting allowed values from the AllowList and documenting
 * them in the API specification.
 */
class IncludeRuleTransformer implements RuleTransformer
{
    /**
     * Determine if this transformer should handle the given rule.
     */
    public function shouldHandle(NormalizedRule $rule): bool
    {
        return $rule->is(IncludeRule::class);
    }

    /**
     * Transform the rule to an OpenAPI schema.
     */
    public function toSchema(Type $previous, NormalizedRule $rule, RuleTransformerContext $context): Type
    {
        /** @var IncludeRule $includeRule */
        $includeRule = $rule->getRule();
        $allowed = $includeRule->getAllowed();

        $schema = new StringType;

        if (! empty($allowed)) {
            $schema->setDescription(
                'Comma-separated list of related resources to include. '.
                'Valid values: `'.implode('`, `', $allowed).'`.'
            );
            // Add example with first few values (up to 3)
            $exampleValues = array_slice($allowed, 0, min(3, count($allowed)));
            $schema->example(implode(',', $exampleValues));
        } else {
            $schema->setDescription(
                'No related resources available for inclusion on this endpoint.'
            );
        }

        return $schema;
    }
}
