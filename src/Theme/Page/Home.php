<?php
namespace Be\App\Cms\Theme\Page;

/**
 * @BeConfig("首页")
 */
class Home
{



    public $northSections = ['Header'];
    public $northSectionsData = [
        [
            'enable' => 1,
            'logoType' => 'text',
            'logoText' => '',
            'logoImage' => '',
            'logoImageMaxWidth' => 0,
            'logoImageMaxHeight' => 0,
            'backgroundColor' => '#fff',
            'paddingTopDesktop' => 40,
            'paddingTopTablet' => 30,
            'paddingTopMobile' => 20,
            'paddingBottomDesktop' => 40,
            'paddingBottomTablet' => 30,
            'paddingBottomMobile' => 20,
        ]
    ];

    public $middleSections = ['Slider', 'Images', 'NewProducts', 'Image'];
    public $middleSectionsData = [
        [
            'enable' => 1,
            'autoplay' => 1,
            'delay' => 3000,
            'speed' => 300,
            'loop' => 1,
            'pagination' => 1,
            'paginationColor' => '#f60',
            'navigation' => 1,
            'navigationColor' => '#f60',
            'navigationSize' => 30,
            'backgroundColor' => '#fff',
            'paddingTopDesktop' => 0,
            'paddingTopTablet' => 0,
            'paddingTopMobile' => 0,
            'paddingBottomDesktop' => 0,
            'paddingBottomTablet' => 0,
            'paddingBottomMobile' => 0,
            'items' => [
                [
                    'name' => 'Image',
                    'data' => [
                        'enable' => 1,
                        'image' => '',
                        'imageMobile' => '',
                        'link' => '#',
                    ],
                ],
                [
                    'name' => 'ImageWithTextOverlay',
                    'data' => [
                        'enable' => 1,
                        'image' => '',
                        'imageMobile' => '',
                        'contentTitle' => 'Slider Title',
                        'contentTitleFontSize' => 40,
                        'contentTitleColor' => '#fff',
                        'contentDescription' => 'Slider Description',
                        'contentDescriptionFontSize' => 16,
                        'contentDescriptionColor' => '#fff',
                        'contentButton' => 'Shop Now',
                        'contentButtonLink' => '#',
                        'contentButtonColor' => '#FFF',
                        'contentWidth' => 400,
                        'contentPosition' => 'left',
                        'contentPositionLeft' => -1,
                        'contentPositionRight' => 30,
                        'contentPositionTop' => 30,
                        'contentPositionBottom' => -1,
                    ],
                ],
            ],
        ],
        [
            'enable' => 1,
            'hoverEffect' => 'rotateScale',
            'backgroundColor' => '#fff',
            'paddingTopDesktop' => 40,
            'paddingTopTablet' => 30,
            'paddingTopMobile' => 20,
            'paddingBottomDesktop' => 40,
            'paddingBottomTablet' => 30,
            'paddingBottomMobile' => 20,
            'spacingDesktop' => 40,
            'spacingTablet' => 30,
            'spacingMobile' => 20,
            'items' => [
                [
                    'name' => 'Image',
                    'data' => [
                        'enable' => 1,
                        'image' => '',
                        'link' => '#',
                    ],
                ],
                [
                    'name' => 'Image',
                    'data' => [
                        'enable' => 1,
                        'image' => '',
                        'link' => '#',
                    ],
                ],
                [
                    'name' => 'Image',
                    'data' => [
                        'enable' => 1,
                        'image' => '',
                        'link' => '#',
                    ],
                ],
            ]
        ],
        [
            'enable' => 1,
            'title' => 'What\'s New',
            'description' => '',
            'quantity' => 10,
            'quantityPerRow' => '5',
            'more' => 'Shop All',
            'marginTopDesktop' => 20,
            'marginTopMobile' => 20,
            'marginLeftRightDesktop' => 0,
            'marginLeftRightMobile' => 0,
            'spacing' => 20,
            'hoverEffect' => 'toggleImage',
            'backgroundColor' => '#f5f5f5',
            'paddingTopDesktop' => 40,
            'paddingTopTablet' => 30,
            'paddingTopMobile' => 20,
            'paddingBottomDesktop' => 40,
            'paddingBottomTablet' => 30,
            'paddingBottomMobile' => 20,
            'spacingDesktop' => 40,
            'spacingTablet' => 30,
            'spacingMobile' => 20,
            'titleAlign' => 'left',
        ],
        [
            'enable' => 1,
            'width' => 'fullWidth',
            'backgroundColor' => '#fff',
            'image' => '',
            'imageMobile' => '',
            'link' => '#',
            'paddingTopDesktop' => 0,
            'paddingTopTablet' => 0,
            'paddingTopMobile' => 0,
            'paddingBottomDesktop' => 0,
            'paddingBottomTablet' => 0,
            'paddingBottomMobile' => 0,
        ]
    ];

    public $southSections = ['Subscribe', 'Footer'];
    public $southSectionsData = [
        [
            'enable' => 1,
            'title' => 'SUBSCRIBE TO OUR NEWSLETTER',
            'description' => 'Get the latest updates on new products and upcoming sales',
            'backgroundColor' => '#FFFFFF',
            'paddingTopDesktop' => 40,
            'paddingTopTablet' => 30,
            'paddingTopMobile' => 20,
            'paddingBottomDesktop' => 40,
            'paddingBottomTablet' => 30,
            'paddingBottomMobile' => 20,
        ],
        [
            'enable' => 1,
            'backgroundColor' => '#FFFFFF',
            'paddingTopDesktop' => 40,
            'paddingTopTablet' => 30,
            'paddingTopMobile' => 20,
            'paddingBottomDesktop' => 40,
            'paddingBottomTablet' => 30,
            'paddingBottomMobile' => 20,
            'items' => [
                [
                    'name' => 'Menu',
                    'data' => [
                        'enable' => 1,
                        'quantity' => 3,
                        'cols' => 3,
                    ],
                ],
                [
                    'name' => 'FollowUs',
                    'data' => [
                        'enable' => 1,
                        'title' => 'Follow Us',
                        'facebook' => '#',
                        'instagram' => '#',
                        'twitter' => '#',
                        'snapchat' => '#',
                        'ticktok' => '#',
                        'youtube' => '#',
                        'cols' => 1,
                    ],
                ],
                [
                    'name' => 'Copyright',
                    'data' => [
                        'enable' => 1,
                        'content' => 'Copyright &copy; All Rights Reserved.',
                        'align' => 'center',
                        'cols' => 2,
                    ],
                ],
                [
                    'name' => 'Payments',
                    'data' => [
                        'enable' => 1,
                        'paypal' => 1,
                        'visa' => 1,
                        'master_card' => 1,
                        'maestro' => 1,
                        'jcb' => 1,
                        'american_express' => 1,
                        'diners_club' => 1,
                        'discover' => 1,
                        'unionpay' => 1,
                        'align' => 'center',
                        'cols' => 2,
                    ],
                ],
            ]
        ]
    ];

}
