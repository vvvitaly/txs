<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors;

use vvvitaly\txs\Core\Export\Data\Transaction;
use Webmozart\Assert\Assert;

/**
 * Replace description with an alias if description contains some key words. It configures with the aliases map:
 *  [
 *      <replacement> => [<keyword1>, <keyword2>, ],
 *      ...
 *      // OR
 *      "<alias and replacement>"
 *  ]
 *
 * If any of keywords (as substring) found in description (case insensitive), all description text replaces with
 * corresponding replacement. If two or more aliases are appropriate, the first matched replacement will be used.
 * If replacement matches only one alias and they are equals they can be set as one string.
 *
 * For example:
 * With this aliases map:
 *  [
 *      'tomatoes' => ['tomato', 'tomatoes'],
 *      'fruits' => 'apples',
 *      'juice'
 *  ]
 * these descriptions will be transformed:
 *  - "Some Tomatoes" => "tomatoes"
 *  - "Best Tomato" => "tomatoes"
 *  - "apples and tomatoes" => "tomatoes",
 *  - "some apples and smth" => "fruits",
 *  - "orange juice" => "juice",
 *  - "juice" => "juice"
 */
final class DescriptionAlias implements ProcessorInterface
{
    /**
     * @var array Aliases map
     */
    private $aliases;

    /**
     * @var array
     */
    private $replacements;

    /**
     * @param array $aliasesMap
     */
    public function __construct(array $aliasesMap)
    {
        $this->aliases = $aliasesMap;
    }

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        $transaction->description = $this->findReplacement($transaction->description);

        foreach ($transaction->splits as $split) {
            $split->memo = $this->findReplacement($split->memo);
        }
    }

    /**
     * @param string|null $text
     *
     * @return string|null
     */
    private function findReplacement(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        if ($this->replacements === null) {
            $this->buildReplacements();
        }

        foreach ($this->replacements as $aliasRegexp => $replacement) {
            if (preg_match($aliasRegexp, $text) === 1) {
                return $replacement;
            }
        }

        return $text;
    }

    /**
     * Build possible replacements
     */
    private function buildReplacements(): void
    {
        $this->replacements = [];

        foreach ($this->aliases as $replacement => $aliasesList) {
            if (is_int($replacement)) {
                Assert::string($aliasesList, 'Replacement can not be an array');

                $replacement = $aliasesList;
            }

            if (!is_array($aliasesList)) {
                $aliasesList = [$aliasesList];
            }

            foreach ($aliasesList as $alias) {
                $regexp = '/\b' . preg_quote($alias, '/') . '\b/iu';
                $this->replacements[$regexp] = $replacement;
            }
        }
    }
}