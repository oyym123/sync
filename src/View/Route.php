<?php

namespace AsyncCenter\View;

use AsyncCenter\Index;

class Route
{
    public function renderView($isBeautyUrl = 0)
    {
        $view = ($_GET['view'] ?? '') ?: 'list';
        $beautyRoute = $isBeautyUrl === 0 ? '.php' : '';
        switch ($view) {
            case 'add':
                require_once 'add.php';
                break;
            case 'list':
                if (isset($_GET['action'])) {
                    require_once __DIR__ . '/../Action.php';
                } else {
                    if (!isset($_GET['date_start'])) {
                        $_GET['date_start'] = date('Y-m-d', strtotime("-1 day"));
                        $_GET['date_end'] = date('Y-m-d');
                    }
                    require_once 'list.php';
                }
                break;
            case 'tccView':
                require_once 'tccView.php';
                break;
            case 'update':
                require_once 'update.php';
                break;
            case 'tccTest':
                (new Index())->tccTest();
                break;
            case 'tcc':
                (new Index())->tcc();
                break;
            default :
                require_once __DIR__ . '/../Action.php';
                break;

        }
    }
}