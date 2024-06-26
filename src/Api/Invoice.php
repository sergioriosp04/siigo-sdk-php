<?php

namespace Epayco\SdkPhpSiigo\Api;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Epayco\SdkPhpSiigo\Api\AbstractApi;
use Epayco\SdkPhpSiigo\Model\Invoice as InvoiceModel;

final class Invoice extends AbstractApi
{
    /**
     * @param array $query ['name' => '', ...]
     * @return ResponseInterface
     * @throws GuzzleException
     * @see https://siigoapi.docs.apiary.io/#reference/facturas-de-venta/listar-facturas
     */
    public function getAll(array $query = []): ResponseInterface
    {
        $query = http_build_query($query);

        return $this
            ->getClient()
            ->request('GET', sprintf('%s/invoices?%s', self::API_VERSION, $query),
        );
    }

    /**
     * @param InvoiceModel $invoice
     * @return ResponseInterface
     * @throws GuzzleException
     * @see https://siigoapi.docs.apiary.io/#reference/facturas-de-venta/crear-factura/crear-factura
     */
    public function create(InvoiceModel $invoice): ResponseInterface
    {
        $data = $this->fillModel($invoice);

        return $this
            ->getClient()
            ->request('POST', sprintf('%s/invoices', self::API_VERSION), $data);
    }

    /**
     * @param string $id
     * @param InvoiceModel $invoice
     * @return ResponseInterface
     * @throws GuzzleException
     * @see https://siigoapi.docs.apiary.io/#reference/facturas-de-venta/edicion-de-facturas-de-venta
     */
    public function update(string $id, InvoiceModel $invoice): ResponseInterface
    {
        $data = $this->fillModel($invoice);

        return $this
            ->getClient()
            ->request('PUT', sprintf('%s/invoices/%s', self::API_VERSION, $id), $data);
    }

    private function fillModel(InvoiceModel $invoice): array
    {
        return [
            "document" => [
                "id" => $invoice->getDocument()->getId(),
            ],
            "date" => $invoice->getDate()->format('Y-m-d'),
            "customer" => [
                "identification" => $invoice->getCustomer()->getIdentification(),
                "branch_office" => $invoice->getCustomer()->getBranchOffice(),
            ],
            "seller" => $invoice->getSeller(),
            "observations" => $invoice->getObservations(),
            "items" => array_map(function ($item) {
                return [
                    "code" => $item->getCode(),
                    "description" => $item->getDescription(),
                    "quantity" => $item->getQuantity(),
                    "price" => $item->getPrice(),
                    "discount" => $item->getDiscount(),
                    "taxes" => array_map(function ($tax) {
                        return [
                            "id" => $tax->getId(),
                        ];
                    }, $item->getTaxes())
                ];
            }, $invoice->getItems()),
            "payments" => array_map(function ($payment) {
                return [
                    "id" => $payment->getId(),
                    "due_date" => $payment->getDueDate()->format('Y-m-d'),
                    "value" => $payment->getValue(),
                ];
            }, $invoice->getPayments()),
            "retentions" => array_map(function ($retention) {
                return [
                    "id" => $retention->getId(),
                ];
            }, $invoice->getGlobalRetentions())
        ];
    }
}