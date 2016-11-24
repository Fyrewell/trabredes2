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
            ->get('/login', [$this, 'login'])
            ->bind('login');

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
            ->get('/doctrine', [$this, 'doctrine'])
            ->bind('doctrine');
        return $controllers;
    }

    public function homepage(App $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    public function login(App $app)
    {
        return $app['twig']->render('login.html.twig', array(
            'error' => $app['security.utils']->getLastAuthenticationError(),
            'username' => $app['security.utils']->getLastUsername(),
        ));
    }

    public function doctrine(App $app)
    {
        return $app['twig']->render('doctrine.html.twig', array(
            'posts' => $app['db']->fetchAll('SELECT * FROM post'),
        ));
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
    
    public function disciplinas(App $app, Request $request)
    {
      return $app['twig']->render('disciplinas.html.twig', array(
            'dados' => $app['db']->fetchAll('SELECT * FROM disciplina')
        ));
    }

    public function pedido(App $app, Request $request)
    {
        return $app['twig']->render('pedido.html.twig', array(
            'dados' => $app['db']->fetchAll('SELECT * FROM pedido')
        ));
    }
    
    public function pedido_add(App $app, Request $request, $id=0)
    {
      $dados = ['id_pedido'=>'','id_cliente'=>'','mensagem'=>''];
      if (!empty($id)){
        $dados = $app['db']->fetchAssoc('SELECT * FROM pedido WHERE id_pedido = ?', [$id]);
      }
        $builder = $app['form.factory']->createBuilder('form');

        $form = $builder
            ->add('id', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'id_pedido', 'value' => $dados['id_pedido'])))
            ->add('id_cliente', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'id_cliente', 'value' => $dados['id_cliente'])))
            ->add('mensagem', 'text', array('constraints' => new Assert\NotBlank(), 'attr' => array('placeholder' => 'mensagem', 'value' => $dados['mensagem'])))
            ->add('submit', 'submit')
            ->getForm()
        ;
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                if (empty($id)){
                  $app['db']->insert('pedido', ['id_cliente' => $_POST['form']['id_cliente'],'mensagem'=>$_POST['form']['mensagem']]);
                }else{
                  $sql = "UPDATE pedido SET id_cliente = ?, mensagem = ?";
                  $app['db']->executeUpdate($sql, [$_POST['form']['id_cliente'],$_POST['form']['mensagem'], $id]);
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
    
    public function pedido_remove(App $app, Request $request, $id=0)
    {
      $app['db']->delete("pedido", ['id_pedido'=>$id]);
      return $app->redirect('../../pedido');
    }
    
    public function form(App $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder('form');

        $choices = array('choice a', 'choice b', 'choice c');

        $form = $builder
            ->add(
                $builder->create('sub-form', 'form')
                    ->add('subformemail1', 'email', array(
                        'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                        'attr' => array('placeholder' => 'email constraints'),
                        'label' => 'A custom label : ',
                    ))
                    ->add('subformtext1', 'text')
            )
            ->add('text1', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array('placeholder' => 'not blank constraints'),
            ))
            ->add('text2', 'text', array('attr' => array('class' => 'span1', 'placeholder' => '.span1')))
            ->add('text3', 'text', array('attr' => array('class' => 'span2', 'placeholder' => '.span2')))
            ->add('text4', 'text', array('attr' => array('class' => 'span3', 'placeholder' => '.span3')))
            ->add('text5', 'text', array('attr' => array('class' => 'span4', 'placeholder' => '.span4')))
            ->add('text6', 'text', array('attr' => array('class' => 'span5', 'placeholder' => '.span5')))
            ->add('text8', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'disabled field')))
            ->add('textarea', 'textarea')
            ->add('email', 'email')
            ->add('integer', 'integer')
            ->add('money', 'money')
            ->add('number', 'number')
            ->add('password', 'password')
            ->add('percent', 'percent')
            ->add('search', 'search')
            ->add('url', 'url')
            ->add('choice1', 'choice', array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('choice2', 'choice', array(
                'choices' => $choices,
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('choice3', 'choice', array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => false,
            ))
            ->add('choice4', 'choice', array(
                'choices' => $choices,
                'multiple' => false,
                'expanded' => false,
            ))
            ->add('country', 'country')
            ->add('language', 'language')
            ->add('locale', 'locale')
            ->add('timezone', 'timezone')
            ->add('date', 'date')
            ->add('datetime', 'datetime')
            ->add('time', 'time')
            ->add('birthday', 'birthday')
            ->add('checkbox', 'checkbox')
            ->add('file', 'file')
            ->add('radio', 'radio')
            ->add('password_repeated', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('required' => true),
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('submit', 'submit')
            ->getForm()
        ;

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $app['session']->getFlashBag()->add('success', 'The form is valid');
            } else {
                $form->addError(new FormError('This is a global error'));
                $app['session']->getFlashBag()->add('info', 'The form is bound, but not valid');
            }
        }

        return $app['twig']->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function cache(App $app)
    {
        $response = new Response($app['twig']->render('cache.html.twig', array('date' => date('Y-M-d h:i:s'))));
        $response->setTtl(10);

        return $response;
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
