<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Search\Api\Data;

interface QueryConfigProviderInterface {

	const WILDCARD_INFIX    = 'infix';

    const WILDCARD_SUFFIX   = 'suffix';

    const WILDCARD_PREFIX   = 'prefix';

    const WILDCARD_DISABLED = 'disabled';

    const MATCH_MODE_AND = 'and';

    const MATCH_MODE_OR  = 'or';

    public function getStoreId(): int;
    
    public function getReplaceWords(): array;
    
    public function getSynonyms(array $terms, int $storeId): array;
    
    public function isStopword(string $term, int $storeId): bool;
    
    public function getMatchMode(): string;
    
    public function getWildcardExceptions(): array;
    
    public function getLongTailExpressions(): array;

    public function applyStemming(string $term): string;

    public function applyLongTail(string $term): string;
}