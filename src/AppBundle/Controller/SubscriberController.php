<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscriber;
use AppBundle\Form\SubscriberType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriberController extends Controller
{

    /**
     * @Route("/subscribers", name="subscribers")
     */
    public function indexAction(Request $request)
    {
        list($sortField, $sortDirection) = $this->prepareListSorting($request);

        $subscribers = $this->get("SubscriberRepository");
        $categories = $this->get("CategoryRepository");

        return $this->render('subscribers/index.html.twig', [
            'subscribers' => $subscribers->sort($sortField, $sortDirection)->findAll(),
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'categories' =>$categories->findAll()
        ]);

        return true;
    }

    /**
     * @Route("/subscribers/create", name="create_subscriber")
     */
    public function createAction(Request $request)
    {
        $subscriber = new Subscriber();

        $categories = $this->get("CategoryRepository")->findAll();
        $attr = array('class' => 'form-control', 'style' => 'margin-bottom:10px');

        $form = $this->createFormBuilder($subscriber)
            ->add('name', TextType::class, ['attr' => $attr])
            ->add('email', EmailType::class, array('attr' => $attr))
            ->add('categories', ChoiceType::class, ['multiple' => true, 'choices' => array_flip($categories), 'attr' => $attr])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-success']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $item = $this->get('SubscriberRepository');
            $item->add($formData);
            $item->save();

            $this->addFlash('success', 'Thank you for subscription!');

            return $this->redirectToRoute('subscribers');
        }

        return $this->render('subscribers/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("subscribers/update/{id}", name="update_subscriber")
     */
    public function updateAction($id, Request $request)
    {
        $subscribers = $this->get("SubscriberRepository");
        $subscriber = $subscribers->find($id);

        $categories = $this->get("CategoryRepository")->findAll();
        $attr = array('class' => 'form-control', 'style' => 'margin-bottom:10px');

        $form = $this->createFormBuilder($subscriber)
            ->add('id', HiddenType::class)
            ->add('name', TextType::class, ['attr' => $attr])
            ->add('email', EmailType::class, array('attr' => $attr))
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-success']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $item = $this->get('SubscriberRepository');
            if ($item->update($formData->getId(), $formData)) {
                $item->save();
                $this->addFlash('success', 'Subscription updated!');
            } else {
                $this->addFlash('error', 'Subscription update failed!');
            }
            return $this->redirectToRoute('subscribers');
        }

        return $this->render('subscribers/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("subscribers/delete/{id}", name="delete_subscriber")
     */
    public function deleteAction($id)
    {
        $subscribers = $this->get('SubscriberRepository');
        $subscribers->delete($id);
        $subscribers->save();

        $this->addFlash('success', 'Deleted!');
        return $this->redirectToRoute('subscribers');
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function prepareListSorting(Request $request): array
    {
        $newSortField = strtolower($request->get('sort', false));
        if (!in_array($newSortField, ['name', 'email', 'created_at'])) {
            $newSortField == false;
        }
        $sortField = $this->get('session')->get('subscribersListSortField', 'created_at');
        $sortDirection = $this->get('session')->get('subscribersListSortDirection', 'asc');

        if ($newSortField && $newSortField == $sortField) {
            $sortDirection = $sortDirection == 'asc' ? 'desc' : 'asc';
        }

        if ($newSortField && $newSortField != $sortField) {
            $sortField = $newSortField;
            $sortDirection = 'asc';
        }

        $this->get('session')->set('subscribersListSortField', $sortField);
        $this->get('session')->set('subscribersListSortDirection', $sortDirection);

        return array($sortField, $sortDirection);
    }
}
