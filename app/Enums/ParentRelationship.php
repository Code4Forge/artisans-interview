<?php

namespace App\Enums;

if (! class_exists(ParentRelationship::class, false)) {
    class_alias(RelationshipStatus::class, 'App\Enums\ParentRelationship');
}
