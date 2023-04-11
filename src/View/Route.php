<?php

namespace AsyncCenter\View;

use AsyncCenter\Index;

class Route
{
    public function renderView()
    {
        preg_match('/\/(.*)?\?/', $_SERVER['REQUEST_URI'], $match);
        $view = $_SERVER['REQUEST_URI'] === '/' ? 'list' : $match[1] ?? 0;
        switch ($view) {
            case 'add':
                include_once 'add.php';
                break;
            case 'list':
                if (isset($_GET['action'])) {
                    include_once __DIR__ . '/../action.php';
                } else {
                    if (!isset($_GET['date_start'])) {
                        $_GET['date_start'] = date('Y-m-d', strtotime("-1 day"));
                        $_GET['date_end'] = date('Y-m-d');
                    }
                    require_once 'list.php';
                }
                break;
            case 'tccView':
                include_once 'tccView.php';
                break;
            case 'update':
                include_once 'update.php';
                break;
            case 'tccTest':
                (new Index())->tccTest();
                break;
            case 'tcc':
                (new Index())->tcc();
                break;
            default :
                include_once __DIR__ . '/../action.php';
                break;

        }
    }
}