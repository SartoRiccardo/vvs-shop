<?php

namespace Webkul\Admin\Helpers;

use Webkul\Sales\Models\Invoice;

class ElectronicInvoice
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    private const CAUSALE_NON_EU =
        'Operazione non soggetta a ritenuta alla fonte a titolo di acconto '
        . 'ai sensi dell\'articolo 1, comma 67, l. n. 190 del 2014 '
        . 'e successive modificazioni';

    private const CAUSALE_EU =
        'Operazione senza applicazione dell\'IVA. Regime di franchigia transfrontaliero '
        . 'dell\'Unione Europea ai sensi della Direttiva (UE) 2020/285 e dell\'art. 1 comma 58 '
        . 'Legge n. 190/2014';

    public static function generate(Invoice $invoice, ?int $seq = null): string
    {
        $sellerVat     = core()->getConfigData('sales.shipping.origin.vat_number') ?? '';
        $sellerCf      = core()->getConfigData('sales.shipping.origin.codice_fiscale') ?? '';
        $sellerName    = core()->getConfigData('sales.shipping.origin.store_name') ?? '';
        $sellerAddrRaw = core()->getConfigData('sales.shipping.origin.address') ?? '';
        $sellerCivic   = '';
        if (preg_match('/^(.*?)\s+(\d+)\s*$/', $sellerAddrRaw, $m)) {
            $sellerAddr  = trim($m[1]);
            $sellerCivic = $m[2];
        } else {
            $sellerAddr = $sellerAddrRaw;
        }
        $sellerCap     = core()->getConfigData('sales.shipping.origin.zipcode') ?? '';
        $sellerCity    = core()->getConfigData('sales.shipping.origin.city') ?? '';
        $sellerProv    = core()->getConfigData('sales.shipping.origin.state') ?? '';
        $sellerCountry = core()->getConfigData('sales.shipping.origin.country') ?? 'IT';
        $sellerEmail   = core()->getConfigData('sales.shipping.origin.email') ?? '';
        $sellerIban    = core()->getConfigData('sales.shipping.origin.iban') ?? '';
        $sellerBank    = core()->getConfigData('sales.shipping.origin.bank_name') ?? '';

        $billing      = $invoice->order->billing_address;
        $buyerCountry = strtoupper($billing->country ?? 'IT');
        $isEu         = in_array($buyerCountry, self::EU_COUNTRIES);

        $regime  = $isEu ? 'RF20' : 'RF19';
        $causale = $isEu ? self::CAUSALE_EU : self::CAUSALE_NON_EU;

        $nameParts = explode(' ', trim($billing->name ?? ''), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $seq           = $seq ?? ($invoice->increment_id ?? $invoice->id);
        $invoiceNumber = 'FPR ' . $seq . '/' . now()->format('y');
        $invoiceDate   = $invoice->created_at->format('Y-m-d');

        $buyerAddress = trim(($billing->postcode ?? '') . ' ' . ($billing->address ?? ''));
        $buyerCity    = $billing->city ?? '';
        if (! empty($billing->state)) {
            $buyerCity .= ', ' . $billing->state;
        }

        $total = number_format((float) $invoice->base_grand_total, 2, '.', '');

        $lineItems = '';
        $lineNum   = 1;
        foreach ($invoice->items as $item) {
            $lineItems .= self::lineItem(
                $lineNum++,
                $item->name,
                number_format((float) $item->qty, 2, '.', ''),
                number_format((float) $item->base_price, 2, '.', ''),
                number_format((float) $item->base_total, 2, '.', ''),
            );
        }

        if ((float) $invoice->base_shipping_amount > 0) {
            $ship = number_format((float) $invoice->base_shipping_amount, 2, '.', '');
            $lineItems .= self::lineItem($lineNum, 'Shipping', '1.00', $ship, $ship);
        }

        $e = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        $numeroCivico = $sellerCivic ? "        <NumeroCivico>{$e($sellerCivic)}</NumeroCivico>" : '';

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<FatturaElettronica versione="FPR12" xmlns="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2">
  <FatturaElettronicaHeader xmlns="">
    <DatiTrasmissione>
      <IdTrasmittente>
        <IdPaese>IT</IdPaese>
        <IdCodice>{$e($sellerVat)}</IdCodice>
      </IdTrasmittente>
      <ProgressivoInvio>{$e($seq)}</ProgressivoInvio>
      <FormatoTrasmissione>FPR12</FormatoTrasmissione>
      <CodiceDestinatario>XXXXXXX</CodiceDestinatario>
    </DatiTrasmissione>
    <CedentePrestatore>
      <DatiAnagrafici>
        <IdFiscaleIVA>
          <IdPaese>IT</IdPaese>
          <IdCodice>{$e($sellerVat)}</IdCodice>
        </IdFiscaleIVA>
        <CodiceFiscale>{$e($sellerCf)}</CodiceFiscale>
        <Anagrafica>
          <Denominazione>{$e($sellerName)}</Denominazione>
        </Anagrafica>
        <RegimeFiscale>{$e($regime)}</RegimeFiscale>
      </DatiAnagrafici>
      <Sede>
        <Indirizzo>{$e($sellerAddr)}</Indirizzo>
        {$numeroCivico}
        <CAP>{$e($sellerCap)}</CAP>
        <Comune>{$e($sellerCity)}</Comune>
        <Provincia>{$e($sellerProv)}</Provincia>
        <Nazione>{$e($sellerCountry)}</Nazione>
      </Sede>
      <Contatti>
        <Email>{$e($sellerEmail)}</Email>
      </Contatti>
    </CedentePrestatore>
    <CessionarioCommittente>
      <DatiAnagrafici>
        <IdFiscaleIVA>
          <IdPaese>{$e($buyerCountry)}</IdPaese>
          <IdCodice>{$e($isEu ? '00000000000' : '99999999999')}</IdCodice>
        </IdFiscaleIVA>
        <Anagrafica>
          <Nome>{$e($firstName)}</Nome>
          <Cognome>{$e($lastName)}</Cognome>
        </Anagrafica>
      </DatiAnagrafici>
      <Sede>
        <Indirizzo>{$e($buyerAddress)}</Indirizzo>
        <CAP>00000</CAP>
        <Comune>{$e($buyerCity)}</Comune>
        <Nazione>{$e($buyerCountry)}</Nazione>
      </Sede>
    </CessionarioCommittente>
  </FatturaElettronicaHeader>
  <FatturaElettronicaBody xmlns="">
    <DatiGenerali>
      <DatiGeneraliDocumento>
        <TipoDocumento>TD01</TipoDocumento>
        <Divisa>EUR</Divisa>
        <Data>{$invoiceDate}</Data>
        <Numero>{$e($invoiceNumber)}</Numero>
        <ImportoTotaleDocumento>{$total}</ImportoTotaleDocumento>
        <Causale>{$e($causale)}</Causale>
      </DatiGeneraliDocumento>
    </DatiGenerali>
    <DatiBeniServizi>
      {$lineItems}
      <DatiRiepilogo>
        <AliquotaIVA>0.00</AliquotaIVA>
        <Natura>N2.2</Natura>
        <ImponibileImporto>{$total}</ImponibileImporto>
        <Imposta>0.00</Imposta>
        <RiferimentoNormativo>Non soggette - altri casi</RiferimentoNormativo>
      </DatiRiepilogo>
    </DatiBeniServizi>
    <DatiPagamento>
      <CondizioniPagamento>TP02</CondizioniPagamento>
      <DettaglioPagamento>
        <ModalitaPagamento>MP05</ModalitaPagamento>
        <DataScadenzaPagamento>{$invoiceDate}</DataScadenzaPagamento>
        <ImportoPagamento>{$total}</ImportoPagamento>
        <IstitutoFinanziario>{$e($sellerBank)}</IstitutoFinanziario>
        <IBAN>{$e($sellerIban)}</IBAN>
      </DettaglioPagamento>
    </DatiPagamento>
  </FatturaElettronicaBody>
</FatturaElettronica>
XML;
    }

    private static function lineItem(int $num, string $desc, string $qty, string $unitPrice, string $total): string
    {
        $e = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        return "
      <DettaglioLinee>
        <NumeroLinea>{$num}</NumeroLinea>
        <Descrizione>{$e($desc)}</Descrizione>
        <Quantita>{$qty}</Quantita>
        <PrezzoUnitario>{$unitPrice}</PrezzoUnitario>
        <PrezzoTotale>{$total}</PrezzoTotale>
        <AliquotaIVA>0.00</AliquotaIVA>
        <Natura>N2.2</Natura>
      </DettaglioLinee>";
    }

    public static function filename(Invoice $invoice, ?int $seq = null): string
    {
        $vat = core()->getConfigData('sales.shipping.origin.vat_number') ?? 'IT';
        $seq = $seq ?? ($invoice->increment_id ?? $invoice->id);

        return sprintf('IT%s_%05d.xml', $vat, $seq);
    }
}
