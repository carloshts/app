<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Feeds Controller
 *
 * @property \App\Model\Table\FeedsTable $Feeds
 *
 */
class FeedsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        //$this->Auth->allow();
        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
        $this->Auth->allow(['add', 'listfeed', 'getbyuserid', 'delete']);
    }

    public function beforeFilter(\Cake\Event\Event $event)
    {
        if ($this->request->params['action'] == 'index') {
            // Fetch related data from all table relations
            $this->Crud->listener('relatedModels')->relatedModels(true);
        }
    }

    public function redirectToAppStore()
    {
        $this->autoRender = false;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $androidDevice = stripos($userAgent, "Android");

        if ($androidDevice) {
            return $this->redirect('https://play.google.com/store/apps/details?id=br.com.clicksoft.blinknet');

        } else {
            return $this->redirect('https://play.google.com/store/apps/details?id=br.com.clicksoft.blinknet');
        }
    }

    public function add()
    {
        $response = [
            'data' => null,
            'status' => 'ok',
            '_serialize' => ['data', 'status'],
        ];

        $feedsTable = TableRegistry::get('feeds');

        if ($this->request->getData('id') != null) {
            $entity = $this->Feeds->get($this->request->getData('id'));
            $entity->picture = $this->request->getData('picture');
            $entity->textpublish = $this->request->getData('textpublish');
        } else {
            $entity = $feedsTable->newEntity($this->request->getData());
            $entity->created = date('Y-m-d H:i:s');
        }

        try {
            if ($this->Feeds->save($entity)) {
                $response['data'] = $entity;
            } else {
                throw new \Exception('Save user error');
            }
        } catch (\Exception $e) {
            // $response['data'] = $e;
            // debug($e);
            // exit;
            $response['data'] = $e;

            $response['status'] = 'error';
        }

        $this->set($response);
        $this->setJsonResponse($response);
    }

    public function delete()
    {
        $response = [
            'data' => null,
            'status' => 'ok',
            '_serialize' => ['data', 'status'],
        ];

        $id = $this->request->query['id'];

        try {
            $entity = $this->Feeds->get($id);
            $result = $this->Feeds->delete($entity);
        } catch (\Exception $e) {
            $response['data'] = $e;
            $response['status'] = 'error';
        }

        $this->set($response);
        $this->setJsonResponse($response);
    }

    public function listfeed()
    {
        $response = [
            'status' => 'ok',
            'data' => [],
            '_serialize' => ['success', 'data', 'status'],
        ];

        $lengthPage = 10;
        $page = $this->request->query['page'];

        $this->paginate = ['limit' => 40];

        $personTable = TableRegistry::get('persons');

        try {
            $query = $this->Feeds->find('all', ['contain' => ['Users']])->order(['created' => 'DESC']);

            $resultData = [];
            $resultQuery = $query->toArray();

            if (count($resultQuery) > 0) {
                
                $indexItem = 0;
                $begin = ($page - 1) * $lengthPage;
                $end = (($page - 1) * $lengthPage) + $lengthPage;

                foreach ($resultQuery as $feed) {
                    if ($begin <= $indexItem && $indexItem < $end) {

                        $person = $personTable->find('all')->where(['user_id = ' => $feed->user_id]);
                        $feed->person = $person;

                        array_push($resultData, $feed);
                    }

                    $indexItem = $indexItem + 1;
                }
            }

            $response['data'] = $resultData;

            // foreach ($query as $feed) {
            //     $person = $personTable->find('all')->where(['user_id = ' => $feed->user_id]);
            //     $feed->person = $person;
            // }

            // $response['data'] = $query;
        } catch (\Exception $e) {
            $response['status'] = $e;
            //$response['status'] = 'error';
        }

        $this->set($response);
        $this->setJsonResponse($response);
    }

    public function getbyuserid()
    {
        $response = [
            'status' => 'ok',
            'data' => [],
            '_serialize' => ['success', 'data', 'status'],
        ];

        $id = $this->request->query['id'];

        $lengthPage = 4;
        $page = $this->request->query['page'];

        try {
            $query = $this->Feeds->find('all')
                        ->where(['user_id = ' => $id])
                        ->order(['created' => 'DESC']);

            $resultData = [];
            $resultQuery = $query->toArray();

            if (count($resultQuery) > 0) {
                
                $indexItem = 0;
                $begin = ($page - 1) * $lengthPage;
                $end = (($page - 1) * $lengthPage) + $lengthPage;

                foreach ($resultQuery as $feed) {
                    if ($begin <= $indexItem && $indexItem < $end) {
                        array_push($resultData, $feed);
                    }

                    $indexItem = $indexItem + 1;
                }
            }

            $response['data'] = $resultData;
        } catch (\Exception $e) {
            $response['status'] = $e;
            //$response['status'] = 'error';
        }

        $this->set($response);
        $this->setJsonResponse($response);
    }
}
