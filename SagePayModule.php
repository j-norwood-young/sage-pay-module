<?php

namespace Crm\SagePayModule;

use Omnipay\Common\CreditCard;
use Crm\ApplicationModule\SeederManager;
use Crm\SagePayModule\Seeders\SagePaySeeder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\PaymentsModule\Repository\PaymentMetaRepository;
use Nette\Application\LinkGenerator;
use Nette\Http\Response;
use Nette\Localization\ITranslator;
use Omnipay\Omnipay;

class SagePayModule extends \Crm\PaymentsModule\Gateways\GatewayAbstract implements \Crm\PaymentsModule\Gateways\RecurrentPaymentInterface
{
    public function __construct(
        LinkGenerator $linkGenerator,
        ApplicationConfig $applicationConfig,
        Response $httpResponse,
        PaymentMetaRepository $paymentMetaRepository,
        ITranslator $translator
    ) {
        parent::__construct($linkGenerator, $applicationConfig, $httpResponse, $translator);
        $this->paymentMetaRepository = $paymentMetaRepository;
    }

    protected function initialize()
    {
        $this->gateway = Omnipay::create('SagePay');

        $this->gateway->setVendor($this->applicationConfig->get('sagepay_vendor'));
        $this->gateway->setTestMode(!($this->applicationConfig->get('sagepay_mode') == 'live'));
    }

    public function begin($payment)
    {
        $this->initialize();

        $card = new CreditCard([
            'firstName' => 'Card',
            'lastName' => 'User',

            'number' => '4929000000006',
            'expiryMonth' => '12',
            'expiryYear' => '2019',
            'CVV' => '123',

            // Billing address details are required.
        ]);

        $this->response = $this->gateway->purchase([
            'amount' => $payment->amount,
            'currency' => $this->applicationConfig->get('currency'),
            'transactionId' => $payment->variable_symbol,
            'card' => $card,
            'returnUrl' => $this->generateReturnUrl($payment) . '?sagepay_success=1&VS=' . $payment->variable_symbol,
            'cancelUrl' => $this->generateReturnUrl($payment) . '?sagepay_success=0&VS=' . $payment->variable_symbol,
            'landingPage' => 'Login',
        ])->send();
    }

    public function complete($payment): ?bool
    {
        $this->initialize();

        $this->response = $this->gateway->completePurchase([
            'amount' => $payment->amount,
            'currency' => $this->applicationConfig->get('currency'),
            'transactionId' => $payment->variable_symbol
        ])->send();

        if ($this->response->isSuccessful()) {
            $responseData = $this->response->getData();
            $this->paymentMetaRepository->add($payment, 'transaction_id', $responseData['PAYMENTINFO_0_TRANSACTIONID']);
        }

        return $this->response->isSuccessful();
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(SagePaySeeder::class));
    }

}