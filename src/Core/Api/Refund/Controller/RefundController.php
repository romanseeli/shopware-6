<?php declare(strict_types=1);

namespace WeArePlanetPayment\Core\Api\Refund\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\{
	HttpFoundation\Request,
	HttpFoundation\Response,
	Routing\Attribute\Route,};
use WeArePlanetPayment\Core\{
	Api\Refund\Service\RefundService,
	Settings\Service\SettingsService};


/**
 * Class RefundController
 *
 * @package WeArePlanetPayment\Core\Api\Refund\Controller
 *
 */
#[Package('sales-channel')]
#[Route(defaults: ['_routeScope' => ['api']])]
class RefundController extends AbstractController {

	/**
	 * @var \WeArePlanetPayment\Core\Api\Refund\Service\RefundService
	 */
	protected $refundService;

	/**
	 * @var \WeArePlanetPayment\Core\Settings\Service\SettingsService
	 */
	protected $settingsService;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * RefundController constructor.
	 *
	 * @param \WeArePlanetPayment\Core\Api\Refund\Service\RefundService $refundService
	 * @param \WeArePlanetPayment\Core\Settings\Service\SettingsService $settingsService
	 */
	public function __construct(RefundService $refundService, SettingsService $settingsService)
	{
		$this->settingsService = $settingsService;
		$this->refundService   = $refundService;
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 * @internal
	 * @required
	 *
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Shopware\Core\Framework\Context          $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \WeArePlanet\Sdk\ApiException
	 * @throws \WeArePlanet\Sdk\Http\ConnectionException
	 * @throws \WeArePlanet\Sdk\VersioningException
	 */
    #[Route("/api/_action/weareplanet/refund/create-refund/",
    	name: "api.action.weareplanet.refund.create-refund",
        methods: ['POST'])]
	public function createRefund(Request $request, Context $context): Response
	{
		$salesChannelId   = $request->request->get('salesChannelId');
		$transactionId    = $request->request->get('transactionId');
		$quantity         = (int) $request->request->get('quantity');
		$lineItemId       = $request->request->get('lineItemId');

		$settings  = $this->settingsService->getSettings($salesChannelId);
		$apiClient = $settings->getApiClient();

		$transaction = $apiClient->getTransactionService()->read($settings->getSpaceId(), $transactionId);
		$this->refundService->create($transaction, $context, $lineItemId, $quantity);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Shopware\Core\Framework\Context          $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \WeArePlanet\Sdk\ApiException
	 * @throws \WeArePlanet\Sdk\Http\ConnectionException
	 * @throws \WeArePlanet\Sdk\VersioningException
	 */
    #[Route("/api/_action/weareplanet/refund/create-refund-by-amount/",
    	name: "api.action.weareplanet.refund.create.refund.by.amount",
        methods: ['POST'])]
	public function createRefundByAmount(Request $request, Context $context): Response
	{
		$salesChannelId   = $request->request->get('salesChannelId');
		$transactionId    = $request->request->get('transactionId');
		$refundableAmount = $request->request->get('refundableAmount');

		$settings  = $this->settingsService->getSettings($salesChannelId);
		$apiClient = $settings->getApiClient();

		$transaction = $apiClient->getTransactionService()->read($settings->getSpaceId(), $transactionId);
		$this->refundService->createRefundByAmount($transaction, $refundableAmount, $context);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}
}
