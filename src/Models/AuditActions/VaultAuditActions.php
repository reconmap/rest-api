<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface VaultAuditActions
{
    public const ITEM_DELETED = 'Vault item deleted';
    public const ITEM_CREATED = 'Vault item created';
    public const ITEM_UPDATED = 'Vault item updated';
}
