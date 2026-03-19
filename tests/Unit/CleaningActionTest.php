<?php

use Laravelldone\DbCleaner\DTOs\CleaningAction;

it('serializes to array correctly', function () {
    $action = new CleaningAction(
        table: 'users',
        column: 'name',
        type: 'whitespace',
        oldValue: ' John ',
        newValue: 'John',
        affectedRows: 5,
        rowIds: [1, 2, 3, 4, 5],
        description: 'Trim whitespace',
    );

    $arr = $action->toArray();

    expect($arr)->toMatchArray([
        'table' => 'users',
        'column' => 'name',
        'type' => 'whitespace',
        'old_value' => ' John ',
        'new_value' => 'John',
        'affected_rows' => 5,
        'row_ids' => [1, 2, 3, 4, 5],
        'description' => 'Trim whitespace',
    ]);
});
