<?php

namespace App;

use Silex\Application as App;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class ControllerProvider implements ControllerProviderInterface
{
    private $app;

    public function connect(App $app)
    {
        date_default_timezone_set('America/Sao_Paulo');
        
        $this->app = $app;

        $app->error([$this, 'error']);

        $controllers = $app['controllers_factory'];

        $controllers
            ->get('/', [$this, 'homepage'])
            ->bind('homepage');

        $controllers
            ->get('/cliente', [$this, 'cliente'])
            ->bind('cliente');
        $controllers
            ->get('/cliente/add/{id}', [$this, 'cliente_add'])
            ->bind('cliente_add/{id}');
        $controllers
            ->post('/cliente/add', [$this, 'cliente_add']);
        $controllers
            ->post('/cliente/add/{id}', [$this, 'cliente_add']);
        $controllers
            ->get('/cliente/add', [$this, 'cliente_add'])
            ->bind('cliente_add');
        $controllers
            ->get('/cliente/remove', [$this, 'cliente_remove'])
            ->bind('cliente_remove');
        $controllers
            ->get('/cliente/remove/{id}', [$this, 'cliente_remove'])
            ->bind('cliente_remove/{id}');
            
        $controllers
            ->get('/pedido', [$this, 'pedido'])
            ->bind('pedido');
        $controllers
            ->get('/pedido/add/{id}', [$this, 'pedido_add'])
            ->bind('pedido_add/{id}');
        $controllers
            ->post('/pedido/add', [$this, 'pedido_add']);
         $controllers
            ->post('/pedido_add_arduino', [$this, 'pedido_add_arduino']);
        $controllers
            ->post('/pedido/add/{id}', [$this, 'pedido_add']);
        $controllers
            ->get('/pedido/add', [$this, 'pedido_add'])
            ->bind('pedido_add');
        $controllers
            ->get('/pedido/remove', [$this, 'pedido_remove'])
            ->bind('pedido_remove');
        $controllers
            ->get('/pedido/remove/{id}', [$this, 'pedido_remove'])
            ->bind('pedido_remove/{id}');
          
        $controllers
            ->post('/pedido_add', [$this, 'pedido_add_arduino']);
          
        return $controllers;
    }

    public function homepage(App $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    public function cliente(App $app, Request $request)
    {
        return $app['twig']->render('cliente.html.twig', array(
            'dados' => $app['db']->fetchAll('SELECT * FROM cliente')
        ));
    }
    
    public function cliente_add(App $app, Request $request, $id=0)
    {
      $dados = ['id_cliente'=>'','nome'=>'','fone'=>'','endereco'=>''];
      if (!empty($id)){
        $dados = $app['db']->fetchAssoc('SELECT * FROM cliente WHERE id_cliente = ?', [$id]);
      }
        $builder = $app['form.factory']->createBuilder('form');

        $form = $builder
            ->add('id', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'id_cliente', 'value' => $dados['id_cliente']))
            )
            ->add('nome', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array('placeholder' => 'nome', 'value' => $dados['nome']),
            ))
            ->add('fone', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'fone', 'value' => $dados['fone'])))
            ->add('endereco', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'endereco', 'value' => $dados['endereco'])))
            ->add('submit', 'submit')
            ->getForm()
        ;
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                if (empty($id)){
                  $app['db']->insert('cliente', ['nome' => $_POST['form']['nome'],'fone'=>$_POST['form']['fone'],'endereco' => $_POST['form']['endereco']]);
                }else{
                  $sql = "UPDATE cliente SET nome = ?, fone = ?, endereco = ? WHERE id_cliente = ?";
                  $app['db']->executeUpdate($sql, [$_POST['form']['nome'],$_POST['form']['fone'],$_POST['form']['endereco'], $id]);
                }
                $app['session']->getFlashBag()->add('success', 'Operação realizada com sucesso');
            } else {
                $form->addError(new FormError('Erro interno'));
                $app['session']->getFlashBag()->add('info', 'O formulario foi recebido porem é invalido');
            }
        }

        return $app['twig']->render('cliente_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function cliente_remove(App $app, Request $request, $id=0)
    {
      $app['db']->delete("cliente", ['id_cliente'=>$id]);
      return $app->redirect('../../cliente');
    }

    public function pedido(App $app, Request $request)
    {
        return $app['twig']->render('pedido.html.twig', array(
            'dados' => $app['db']->fetchAll('SELECT * FROM pedido')
        ));
    }
    
    public function pedido_add(App $app, Request $request, $id=0)
    {
      $dados = ['id_pedido'=>'','id_cliente'=>'','mensagem'=>'', 'data_hora'=>''];
      if (!empty($id)){
        $dados = $app['db']->fetchAssoc('SELECT * FROM pedido WHERE id_pedido = ?', [$id]);
      }
        $builder = $app['form.factory']->createBuilder('form');

        $form = $builder
            ->add('id', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'id_pedido', 'value' => $dados['id_pedido'])))
            ->add('id_cliente', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'id_cliente', 'value' => $dados['id_cliente'])))
            ->add('mensagem', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'mensagem', 'value' => $dados['mensagem'])))
            ->add('data_hora', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'data_hora', 'value' => $dados['data_hora'])))
            ->add('submit', 'submit')
            ->getForm()
        ;
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                if (empty($id)){
                  $app['db']->insert('pedido', ['id_cliente' => $_POST['form']['id_cliente'],'mensagem'=>$_POST['form']['mensagem'],'data_hora'=>$_POST['form']['data_hora']]);
                }else{
                  $sql = "UPDATE pedido SET id_cliente = ?, mensagem = ?, data_hora = ?";
                  $app['db']->executeUpdate($sql, [$_POST['form']['id_cliente'],$_POST['form']['mensagem'],$_POST['form']['data_hora'], $id]);
                }
                $app['session']->getFlashBag()->add('success', 'Operação realizada com sucesso');
            } else {
                $form->addError(new FormError('Erro interno'));
                $app['session']->getFlashBag()->add('info', 'O formulario foi recebido porem é invalido');
            }
        }

        return $app['twig']->render('pedido_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    
    public function pedido_add_arduino(App $app, Request $request)
    {
      $dt = date("Y-m-d H:i:s");
      $dados = $app['db']->fetchAssoc("SELECT * FROM cliente
                WHERE fone = ? ", [$_POST['numero']]);

      if (count($dados)>1){
        try{
            $app['db']->insert('pedido', ['id_cliente' => $dados['id_cliente'], 'data_hora' => $dt, 'mensagem' => $_POST['MSG_Texto']]);
            return new Response('ok');
          }catch(\Exception $e){
            return new Response('error');
          }
        return new Response('ok');
      }else
        return new Response('nok');
    }
    
    
    public function pedido_remove(App $app, Request $request, $id=0)
    {
      $app['db']->delete("pedido", ['id_pedido'=>$id]);
      return $app->redirect('../../pedido');
    }

    public function error(\Exception $e, $code)
    {
        if ($this->app['debug']) {
            return;
        }

        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return new Response($message, $code);
    }
}
