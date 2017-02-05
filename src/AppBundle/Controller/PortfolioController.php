<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Portfolio;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portfolio controller.
 *
 * @Route("portfolio")
 */
class PortfolioController extends Controller
{
    /**
     * Lists all portfolio entities.
     *
     * @Route("/", name="portfolio_index")
     * @Method("GET")
     * @return type
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $portfolios = $em->getRepository('AppBundle:Portfolio')->findBy(array ('userid' => $this->getUser()));

        return $this->render('portfolio/index.html.twig', array(
            'portfolios' => $portfolios,
        ));
    }

    /**
     * Creates a new portfolio entity.
     *
     * @Route("/new", name="portfolio_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return type
     */
    public function newAction(Request $request)
    {
        $portfolio = new Portfolio();

        $portfolio->setUserid($this->getUser());

        $form = $this->createForm('AppBundle\Form\PortfolioType', $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($portfolio);
            $em->flush($portfolio);

            return $this->redirectToRoute('portfolio_show', array('id' => $portfolio->getId()));
        }

        return $this->render('portfolio/new.html.twig', array(
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a portfolio entity.
     *
     * @Route("/{id}", name="portfolio_show")
     * @Method("GET")
     * @param Portfolio $portfolio
     * @return type
     * @throws type
     */
    public function showAction(Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        $deleteForm = $this->createDeleteForm($portfolio);

        $stockManager = $this->get('app.stock_manager');

        $stocks = $stockManager->getLastStocks($portfolio);

        return $this->render('portfolio/show.html.twig', array(
            'portfolio' => $portfolio,
            'stocks' => $stocks,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing portfolio entity.
     *
     * @Route("/{id}/edit", name="portfolio_edit")
     * @Method({"GET", "POST"})
     * @param Request   $request
     * @param Portfolio $portfolio
     * @return type
     * @throws type
     */
    public function editAction(Request $request, Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        $deleteForm = $this->createDeleteForm($portfolio);
        $editForm = $this->createForm('AppBundle\Form\PortfolioType', $portfolio);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('portfolio_show', array('id' => $portfolio->getId()));
        }

        return $this->render('portfolio/edit.html.twig', array(
            'portfolio' => $portfolio,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/chart", name="portfolio_chart")
     * @Method({"GET", "POST"})
     * @param Portfolio $portfolio
     * @return type
     * @throws type
     */
    public function showChartAction(Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        return $this->render('portfolio/chart.html.twig', array(
            'portfolio' => $portfolio,
        ));
    }

    /**
     * @Route("/{id}/chart/json", name="portfolio_chart_json")
     * @Method({"GET", "POST"})
     * @param Portfolio $portfolio
     * @return Response
     * @throws type
     */
    public function chartJsonAction(Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        $startDate = '2015-01-01';
        $endDate = '2017-02-04';

        $stocksSum = $this->get('app.stock_manager')->getStocksSum($portfolio, $startDate, $endDate);

        $highchartsJson = $this->get('app.stock_manager')->getHighchartsJson($stocksSum);

        // можно было бы использовать JsonResponse(),
        // но плагин графика Highcharts требует нестандартный JSON
        // подробнее в функции getHighchartsJson()
        $response = new Response();

        $response->setContent($highchartsJson);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Возвращает список портфелей
     *
     * Нужно для верхнего меню (раздел «Портфели»)
     *
     * @return type
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $portfolios = $em->getRepository('AppBundle:Portfolio')->findBy(array ('userid' => $this->getUser()));

        return $this->render('portfolio/list.html.twig', array(
            'portfolios' => $portfolios,
        ));
    }

    /**
     * Deletes a portfolio entity.
     *
     * @Route("/{id}", name="portfolio_delete")
     * @Method("DELETE")
     * @param Request   $request
     * @param Portfolio $portfolio
     * @return type
     * @throws type
     */
    public function deleteAction(Request $request, Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        $form = $this->createDeleteForm($portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($portfolio);
            $em->flush($portfolio);
        }

        return $this->redirectToRoute('portfolio_index');
    }

    /**
     * Creates a form to delete a portfolio entity.
     *
     * @param Portfolio $portfolio The portfolio entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Portfolio $portfolio)
    {
        if (!$portfolio->isOwner($this->getUser())) {
            throw $this->createAccessDeniedException('Нет доступа');
        }

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('portfolio_delete', array('id' => $portfolio->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
