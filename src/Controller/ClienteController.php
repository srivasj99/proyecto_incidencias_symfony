<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Cliente;
use App\Entity\Incidencia;
use App\Entity\Usuario;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ClienteController extends AbstractController {

    /**
     * @Route("/cliente", name="cliente")
     */
    public function index(ManagerRegistry $doctrine): Response {

        $repositorioCliente = $doctrine->getRepository(Cliente::class);
        $cliente = $repositorioCliente->findAll();

        return $this->render('cliente/index.html.twig', [
                    'controller_name' => 'ClienteController',
                    'cliente' => $cliente
        ]);
    }

    /**
     * @Route("/cliente/{id<\d+>}", name="ver_cliente")
     */
    public function ver_cliente(ManagerRegistry $doctrine, Cliente $cliente): Response {
        $repositorioCliente = $doctrine->getRepository(Cliente::class);
        $cliente = $repositorioCliente->find($cliente);

        return $this->render('cliente/ver_cliente.html.twig', [
                    'controller_name' => 'ClienteController',
                    'cliente' => $cliente
        ]);
    }

    /**
     * 
     * @Route("/cliente/borrar/{id<\d+>}", name="borrar_cliente")
     */
    public function borrar(Cliente $cliente, ManagerRegistry $doctrine): Response {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $em = $doctrine->getManager();
        $em->remove($cliente);
        $em->flush();
        $this->addFlash("aviso", "Cliente borrado");
        return $this->redirectToRoute("cliente");
    }

    /**
     * Inserta un post utilizando los formularios de symfony
     * @Route("/cliente/insertar", name="insertar_cliente")
     */
    public function insertar(Request $request, ManagerRegistry $doctrine): Response {
        $cliente = new Cliente();
        $form = $this->createFormBuilder($cliente)
                ->add("nombre", TextType::class)
                ->add("apellidos", TextType::class)
                ->add("telefono", TextType::class)
                ->add("direccion", TextType::class)
                ->add('Insertar', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cliente = $form->getData();
            $em = $doctrine->getManager();
            $em->persist($cliente);
            $em->flush();
            $this->addFlash("aviso", "Cliente insertado");
            return $this->redirectToRoute("cliente");
        }
        return $this->renderForm('cliente/insertarCliente.html.twig', ['form_cliente' => $form]);
    }

    /**
     * @Route("/cliente/{id<\d+>}/insertar_incidencia", name="insertarIncidencia")
     */
    public function insertarIncidencia(Request $request, ManagerRegistry $doctrine, Cliente $cliente): Response {
        $incidencia = new Incidencia();
        $form = $this->createFormBuilder($incidencia)
                ->add("titulo", TextType::class)
                ->add("insertar", SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $incidencia = $form->getData();
            $incidencia->setCliente($cliente);
            $incidencia->setFechaCreacion(new \DateTime());
            $incidencia->setEstado("iniciada");
            $incidencia->setUsuario($this->getUser());
            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();
            $this->addFlash("aviso", "Incidencia insertada");
            return $this->redirectToRoute("ver_cliente", ['id' => $cliente->getId()]);
        }
        return $this->renderForm('cliente/insertarIncidencia.html.twig', ['form_incidencia' => $form]);
    }

    /**
     * @Route("/cliente/editar_incidencia/{id<\d+>}", name="editarIncidencia")
     */
    public function editarIncidencia(Request $request, ManagerRegistry $doctrine, Incidencia $incidencia): Response {

        $form = $this->createFormBuilder($incidencia)
                ->add("titulo", TextType::class)
                ->add('estado', ChoiceType::class, [
                    'choices' => [
                        'iniciada' => 'iniciada',
                        'en proceso' => 'en proceso',
                        'resuelta' => 'resuelta',
                    ]
                ])
                ->add("insertar", SubmitType::class)
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $incidencia = $form->getData();
            $em->flush();
            $this->addFlash("aviso", "Incidencia editada");
            return $this->redirectToRoute("cliente");
        }

        return $this->renderForm('cliente/editarIncidencia.html.twig', ['form_incidencia' => $form]);
    }

    /**
     * @Route("/cliente/borrar_incidencia/{id<\d+>}", name="borrarIncidencia")
     */
    public function borrarIncidencia(Request $request, ManagerRegistry $doctrine, Incidencia $incidencia): Response {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $em = $doctrine->getManager();
        $em->remove($incidencia);
        $em->flush();
        $this->addFlash("aviso", "Incidencia borrada");
        return $this->redirectToRoute("cliente");
    }

    /**
     * @Route("/incidencias", name="incidencias")
     */
    public function verIncidencias(Request $request, ManagerRegistry $doctrine): Response {
        $repositorioIncidencias = $doctrine->getRepository(Incidencia::class);
        $incidencia = $repositorioIncidencias->findBy(array(), array('fechaCreacion' => 'DESC'));

        return $this->render('cliente/ver_incidencias.html.twig', [
                    'controller_name' => 'ClienteController',
                    'incidencia' => $incidencia
        ]);
    }

    /**
     * @Route("/incidencias/{id<\d+>}", name="ver_incidencia")
     */
    public function ver_incidencia(ManagerRegistry $doctrine, Incidencia $incidencia): Response {
        $repositorioIncidencia = $doctrine->getRepository(Incidencia::class);
        $incidencia = $repositorioIncidencia->find($incidencia);

        return $this->render('cliente/incidencia.html.twig', [
                    'controller_name' => 'ClienteController',
                    'incidencia' => $incidencia
        ]);
    }

    /**
     * @Route("/incidencias/crear", name="crear_incidencia")
     */
    public function crear_incidencia(ManagerRegistry $doctrine, Request $request): Response {
        $incidencia = new Incidencia();
        $form = $this->createFormBuilder($incidencia)
                ->add("titulo", TextType::class)
                ->add('cliente', EntityType::class, [
                    'class' => Cliente::class,
                    'choice_label' => 'nombre'
                ])
                ->add('insertar', SubmitType::class)
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $incidencia = $form->getData();
            $incidencia->setFechaCreacion(new \DateTime());
            $incidencia->setEstado("iniciada");
            $incidencia->setUsuario($this->getUser());
            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();
            $this->addFlash("aviso", "Incidencia insertada");
            return $this->redirectToRoute("incidencias");
        }
        return $this->renderForm("cliente/crearIncidencia.html.twig", ["form_incidencia" => $form]);
    }

}
