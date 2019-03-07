<?php

namespace App\Modules\Gallery\Controllers;

use App\Modules\Gallery\Models\Album;
use App\Modules\Gallery\Models\Photo;
use T4\Core\Std;
use T4\Fs\Helpers;
use T4\Http\E404Exception;
use T4\Mvc\Controller;
use T4\Orm\ModelDataProvider;


class Index extends Controller
{
    private $total_foto;

    public function actionDefault( $page = 1)
    {
        $this->data->provider = new ModelDataProvider(Album::class, [
            'where' => '__prt = 0',
            'order' => 'published DESC',
        ]);
        $this->data->page = $page;
    }


    public function actiongetAlbumByUrl($url, $count=6,$page=1)
    {
        $album = Album::findByUrl($url);


        if (empty($album))
            throw new E404Exception;
        $this->total_foto = Photo::countAllByColumn('__album_id', $album->Pk);

        if("json" == $this->app->request->extension) {


            if ($album->__rgt - $album->__lft > 1) {
                $items = Album::findAllByQuery('SELECT * FROM albums WHERE __lft >' . $album->__lft . ' AND __rgt <' . $album->__rgt);
            } else {

                $paginator['total'] = $this->total_foto;
                $paginator['count'] = $count;
                $paginator['page'] = $page;

                $items = Photo::findAllByColumn('__album_id', $album->Pk, [
                    'order' => 'published DESC',
                    'offset' => ($page - 1) * $count,
                    'limit' => $count,
                ]);

                $data = [];
                foreach ($items as $sub_item => $value) {

                    $data[] = $value->getData();

                }

                $this->data->items = $data;
                $this->data->paginator=$paginator;
            }
        } else {
            $this->data->album = $album;

            $this->data->total = ceil($this->total_foto / $count);
        }


    }

    public function actionPhoto($id, $album_id)
    {
        $album = $this->data->album = Album::findByColumn('__id', $album_id);
        if ($album->__prt) {
            $this->data->albumParent = Album::findByColumn('__id', $album->__prt);
        }
        $this->data->item = Photo::findByColumn('__id', $id);
    }


    public function actionLastAddedPhoto()
    {
        $this->data->item = Photo::findByQuery('SELECT * FROM photos ORDER BY published DESC');
    }

    public function actionLastAddedPhotos($album_id, $num)
    {
        $this->data->items = Photo::findAllByQuery('SELECT * FROM photos WHERE __album_id=' . $album_id . ' ORDER BY published DESC LIMIT ' . $num);
    }

    public function actionRandomPhoto($album_id)
    {
        $this->data->item = Photo::findByQuery('SELECT * FROM photos WHERE __album_id=' . $album_id . ' ORDER BY rand()');
    }
}