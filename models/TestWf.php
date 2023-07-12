<?php namespace Waka\Workflow\Models;

use Model;

/**
 * UserCreateable Model
 */
class TestWf extends Model
{
    use \Waka\Workflow\Classes\Traits\WakaWorkflowTrait;

    public $implement = [
        'October.Rain.Database.Behaviors.Purgeable',
    ];
    public $purgeable = [
    ];
    public $attributesToDs = [
    ];
}
