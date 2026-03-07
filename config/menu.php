<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Navigation Menu
    |--------------------------------------------------------------------------
    |
    | This array is for Navigation menus of the backend.  Just add/edit or
    | remove the elements from this array which will automatically change the
    | navigation.
    |
    */

    // SIDEBAR LAYOUT - MENU

    'sidebar' => [
        [
            'title' => 'Dashboard',
            'link'  => '/admin',
            'active' => 'admin',
            'icon'  => 'fa fa-dashboard',
        ],
        [
            'title' => 'Admin Users',
            'link'  => '/admin/users',
            'active' => 'admin/users*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Business Category',
            'link'  => '/admin/businesscategory',
            'active' => 'admin/businesscategory*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Product Category',
            'link'  => '/admin/productcategory',
            'active' => 'admin/productcategory*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'ID Proof Type',
            'link'  => '/admin/idprooftype',
            'active' => 'admin/idprooftype*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'Service Type',
            'link'  => '/admin/servicetype',
            'active' => 'admin/servicetype*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'Bank',
            'link'  => '/admin/bank',
            'active' => 'admin/bank*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Rate Type',
            'link'  => '/admin/ratetype',
            'active' => 'admin/ratetype*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'Vendor List',
            'link'  => '/admin/vendor',
            'active' => 'admin/vendor*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'Customer List',
            'link'  => '/admin/customer',
            'active' => 'admin/customer*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'General Message',
            'link'  => '/admin/generalmessage',
            'active' => 'admin/generalmessage*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Notification Message',
            'link'  => '/admin/notificationmessage',
            'active' => 'admin/notificationmessage*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Send General Notification',
            'link'  => '/admin/generalnotification',
            'active' => 'admin/generalnotification*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Country Management',
            'link'  => '/admin/country',
            'active' => 'admin/country*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'State Management',
            'link'  => '/admin/state',
            'active' => 'admin/state*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'City Management',
            'link'  => '/admin/city',
            'active' => 'admin/city*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Version Management',
            'link'  => '/admin/version',
            'active' => 'admin/version*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'System Settings',
            'link'  => '/admin/setting',
            'active' => 'admin/setting*',
            'icon'  => 'fa fa-user',
        ],
		[
            'title' => 'Email Templates',
            'link'  => '/admin/emailtemplate',
            'active' => 'admin/emailtemplate*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'How Did You Know',
            'link'  => '/admin/howdidyouknow',
            'active' => 'admin/howdidyouknow*',
            'icon'  => 'fa fa-user',
        ],
        [
            'title' => 'Languages',
            'link'  => '/admin/languages',
            'active' => 'admin/languages*',
            'icon'  => 'fa fa-user',
        ],
        
    ],

    // HORIZONTAL MENU LAYOUT -  MENU

    'horizontal' => [
        [
            'title' => 'Dashboard',
            'link'  => '/admin',
            'active' => 'admin',
            'icon'  => 'fa fa-dashboard',
        ],
        [
            'title' => 'Settings',
            'link'  => '/admin/settings',
            'active' => 'admin/settings*',
            'icon'  => 'fa fa-cogs',
        ],
        [
            'title' => 'Users',
            'link'  => '#',
            'active' => 'admin/users*',
            'icon'  => 'fa fa-user',
            'children' => [
                [
                    'title' => 'All Users',
                    'link'  => '/admin/users',
                    'active' => 'admin/users',
                ],
                [
                    'title' => 'User Profile',
                    'link'  => '/admin/users/1',
                    'active' => 'admin/users/*',
                ]
            ]
        ],
    ]
];
