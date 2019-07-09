<?php


namespace App\Modules\Menu\Controllers;

use App\Modules\Menu\Models\MenuItem;
use T4\Mvc\Controller;
use T4\Core\Std;

class Index extends Controller
{
    public function actionDefault()
    {
        //$this->data->items = MenuItem::findAllTree();
        $this->data->items = MenuItem::getToGSTree();




    }

}