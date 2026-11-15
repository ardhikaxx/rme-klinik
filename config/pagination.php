<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Rows Per Table Page
    |--------------------------------------------------------------------------
    |
    | Every data table in RME KLINIK starts on this many rows. The value is
    | resolved centrally so that all modules stay consistent with each other.
    |
    */

    'default' => 50,

    /*
    |--------------------------------------------------------------------------
    | Selectable Rows Per Table Page
    |--------------------------------------------------------------------------
    |
    | The choices offered in the table footer dropdown. Any value outside of
    | this list falls back to the default above.
    |
    */

    'options' => [25, 50, 100, 250],

];
