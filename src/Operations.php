<?php

declare(strict_types=1);

// Generated from the W System OpenAPI 0.3.131 by growth/sdk/gen.py. Do not edit by hand.

namespace WSystem;

/** All 57 operations of the W System API (spec 0.3.131). */
abstract class Operations
{
    public const SPEC_VERSION = '0.3.131';
    /** @var list<array{0:string,1:string,2:string}> operationId, method, path */
    public const OPERATIONS = [
        ['billingPlans', 'GET', '/billing/plans'],
        ['billingUsage', 'GET', '/billing/usage'],
        ['billingCheckout', 'POST', '/billing/checkout'],
        ['einvoiceMandateTerms', 'GET', '/einvoice/mandate-terms'],
        ['createPeppolMandate', 'POST', '/einvoice/mandates'],
        ['getPeppolMandate', 'GET', '/einvoice/mandates/{mid}'],
        ['revokePeppolMandate', 'POST', '/einvoice/mandates/{mid}/revoke'],
        ['einvoiceInbox', 'GET', '/einvoice/inbox'],
        ['einvoiceInboxUbl', 'GET', '/einvoice/inbox/{document_id}/ubl'],
        ['einvoiceLiveStatus', 'GET', '/einvoice/live/{document_id}'],
        ['listPlatformCompanies', 'GET', '/platform/companies'],
        ['getPlatformCompany', 'GET', '/platform/companies/{mandate_id}'],
        ['setPlatformWebhook', 'POST', '/platform/webhook'],
        ['getPlatformWebhook', 'GET', '/platform/webhook'],
        ['deletePlatformWebhook', 'DELETE', '/platform/webhook'],
        ['testPlatformWebhook', 'POST', '/platform/webhook/test'],
        ['getPlatformUsage', 'GET', '/platform/usage'],
        ['setPlatformSpendCap', 'PUT', '/platform/spend-cap'],
        ['checkReceivedInvoice', 'POST', '/einvoice/check'],
        ['listInboxInvoices', 'GET', '/inbox/invoices'],
        ['getInboxInvoice', 'GET', '/inbox/invoices/{iid}'],
        ['deleteInboxInvoice', 'DELETE', '/inbox/invoices/{iid}'],
        ['getInboxAddress', 'GET', '/inbox/address'],
        ['rotateInboxAddress', 'POST', '/inbox/address/rotate'],
        ['ksefInfo', 'GET', '/einvoice/ksef'],
        ['ksefConnect', 'POST', '/einvoice/ksef/connections'],
        ['ksefConnections', 'GET', '/einvoice/ksef/connections'],
        ['ksefTestCompany', 'POST', '/einvoice/ksef/test-company'],
        ['ksefAuthChallenge', 'POST', '/einvoice/ksef/auth/challenge'],
        ['ksefAuthComplete', 'POST', '/einvoice/ksef/auth/complete'],
        ['ksefDisconnect', 'DELETE', '/einvoice/ksef/connections/{cid}'],
        ['ksefInvoiceStatus', 'GET', '/einvoice/ksef/invoices/{sid}'],
        ['ksefInvoiceUpo', 'GET', '/einvoice/ksef/invoices/{sid}/upo'],
        ['ksefInvoices', 'GET', '/einvoice/ksef/invoices'],
        ['health', 'GET', '/health'],
        ['viesStatus', 'GET', '/vies/status'],
        ['verifyVat', 'GET', '/verify'],
        ['verifyVatBatch', 'POST', '/verify/batch'],
        ['lookupCompany', 'GET', '/company'],
        ['lookupCompanyBatch', 'POST', '/company/batch'],
        ['screenSanctions', 'GET', '/sanctions/screen'],
        ['screenSanctionsBatch', 'POST', '/sanctions/screen/batch'],
        ['kybCheck', 'POST', '/kyb/check'],
        ['verifyEori', 'GET', '/eori'],
        ['verifyEoriBatch', 'POST', '/eori/batch'],
        ['validateEinvoice', 'POST', '/einvoice/validate'],
        ['parseEinvoice', 'POST', '/einvoice/parse'],
        ['createEinvoice', 'POST', '/einvoice/create'],
        ['einvoiceRoute', 'GET', '/einvoice/route'],
        ['ddReport', 'POST', '/ddreport'],
        ['frPaRegistry', 'GET', '/fr/pa-registry'],
        ['extractEinvoiceFromPdf', 'POST', '/einvoice/extract'],
        ['einvoiceSample', 'GET', '/einvoice/sample'],
        ['sendEinvoice', 'POST', '/einvoice/send'],
        ['preflightEinvoice', 'POST', '/einvoice/preflight'],
        ['einvoiceEvidenceKey', 'GET', '/einvoice/evidence-key'],
        ['einvoiceUsage', 'GET', '/einvoice/usage'],
    ];

    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $options headers, timeout, requestId, idempotencyKey, maxRetries
     */
    abstract public function request(string $method, string $path, array $query = [], mixed $body = null, array $options = []): mixed;

    /** Monthly API-key plans  (GET /billing/plans) */
    public function billingPlans(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/billing/plans', array_merge([], $params), null, $options);
    }

    /** Your API key's plan, monthly quota, units used, and paid x402 calls attributed to this key (free; no units consumed)  (GET /billing/usage) */
    public function billingUsage(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/billing/usage', array_merge([], $params), null, $options);
    }

    /** Start Stripe Checkout for a monthly plan  (POST /billing/checkout) */
    public function billingCheckout(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/billing/checkout', array_merge([], $params), $body, $options);
    }

    /** Get the Peppol mandate terms to show the signer  (GET /einvoice/mandate-terms) */
    public function einvoiceMandateTerms(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/mandate-terms', array_merge([], $params), null, $options);
    }

    /** Record a company's Peppol mandate (click-through acceptance)  (POST /einvoice/mandates) */
    public function createPeppolMandate(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/mandates', array_merge([], $params), $body, $options);
    }

    /** Get one of your Peppol mandate records  (GET /einvoice/mandates/{mid}) */
    public function getPeppolMandate(string|int|float|bool $mid, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/mandates/' . rawurlencode((string) $mid), array_merge([], $params), null, $options);
    }

    /** Revoke a Peppol mandate  (POST /einvoice/mandates/{mid}/revoke) */
    public function revokePeppolMandate(string|int|float|bool $mid, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/mandates/' . rawurlencode((string) $mid) . '/revoke', array_merge([], $params), null, $options);
    }

    /** LIVE: documents received over Peppol for your mandated companies (X-API-Key)  (GET /einvoice/inbox) */
    public function einvoiceInbox(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/inbox', array_merge([], $params), null, $options);
    }

    /** LIVE: signed download link for a received document's original UBL (valid 1 hour)  (GET /einvoice/inbox/{document_id}/ubl) */
    public function einvoiceInboxUbl(string|int|float|bool $documentId, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/inbox/' . rawurlencode((string) $documentId) . '/ubl', array_merge([], $params), null, $options);
    }

    /** LIVE: delivery state of a document you sent with mode=live (TRANSIT -> SENT, or FAILED = not charged)  (GET /einvoice/live/{document_id}) */
    public function einvoiceLiveStatus(string|int|float|bool $documentId, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/live/' . rawurlencode((string) $documentId), array_merge([], $params), null, $options);
    }

    /** List the client companies under your key: onboarding stage, live readiness and per-company live document counts  (GET /platform/companies) */
    public function listPlatformCompanies(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/platform/companies', array_merge([], $params), null, $options);
    }

    /** One client company under your key: onboarding stage, next step and live document counts  (GET /platform/companies/{mandate_id}) */
    public function getPlatformCompany(string|int|float|bool $mandateId, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/platform/companies/' . rawurlencode((string) $mandateId), array_merge([], $params), null, $options);
    }

    /** Set or replace your webhook URL for company.stage_changed (signing secret returned once)  (POST /platform/webhook) */
    public function setPlatformWebhook(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/platform/webhook', array_merge([], $params), $body, $options);
    }

    /** Your webhook URL and its recent deliveries (the secret is never shown again)  (GET /platform/webhook) */
    public function getPlatformWebhook(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/platform/webhook', array_merge([], $params), null, $options);
    }

    /** Remove your webhook  (DELETE /platform/webhook) */
    public function deletePlatformWebhook(array $params = [], array $options = []): mixed
    {
        return $this->request('DELETE', '/platform/webhook', array_merge([], $params), null, $options);
    }

    /** Send a signed webhook.test event to your URL now and return the delivery result  (POST /platform/webhook/test) */
    public function testPlatformWebhook(array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/platform/webhook/test', array_merge([], $params), null, $options);
    }

    /** Platform plan: this month's checked documents, Peppol documents, active companies and cost estimate (free)  (GET /platform/usage) */
    public function getPlatformUsage(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/platform/usage', array_merge([], $params), null, $options);
    }

    /** Platform plan: set or remove an optional monthly spend cap in EUR (calls that would exceed it get 429, nothing charged)  (PUT /platform/spend-cap) */
    public function setPlatformSpendCap(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('PUT', '/platform/spend-cap', array_merge([], $params), $body, $options);
    }

    /** Check a received invoice before paying it: e-invoice rules, supplier VAT + sanctions, amounts, IBAN, duplicates, bank-detail changes -> ok / review / hold  (POST /einvoice/check) */
    public function checkReceivedInvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/check', array_merge([], $params), $body, $options);
    }

    /** Your checked received invoices, newest first (free)  (GET /inbox/invoices) */
    public function listInboxInvoices(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/inbox/invoices', array_merge([], $params), null, $options);
    }

    /** One checked invoice with its full check result (free)  (GET /inbox/invoices/{iid}) */
    public function getInboxInvoice(string|int|float|bool $iid, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/inbox/invoices/' . rawurlencode((string) $iid), array_merge([], $params), null, $options);
    }

    /** Delete one invoice from your inbox history (free)  (DELETE /inbox/invoices/{iid}) */
    public function deleteInboxInvoice(string|int|float|bool $iid, array $params = [], array $options = []): mixed
    {
        return $this->request('DELETE', '/inbox/invoices/' . rawurlencode((string) $iid), array_merge([], $params), null, $options);
    }

    /** Your own email intake address: forward supplier invoices there (free)  (GET /inbox/address) */
    public function getInboxAddress(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/inbox/address', array_merge([], $params), null, $options);
    }

    /** Issue a new intake address; the old one stops working (free)  (POST /inbox/address/rotate) */
    public function rotateInboxAddress(array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/inbox/address/rotate', array_merge([], $params), null, $options);
    }

    /** Poland KSeF connector: what works, environment, how to connect (free)  (GET /einvoice/ksef) */
    public function ksefInfo(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/ksef', array_merge([], $params), null, $options);
    }

    /** Connect a company's KSeF account (TEST, or DEMO if enabled): store its KSeF token encrypted after a sign-in check  (POST /einvoice/ksef/connections) */
    public function ksefConnect(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/ksef/connections', array_merge([], $params), $body, $options);
    }

    /** List the KSeF connections (companies) under your API key  (GET /einvoice/ksef/connections) */
    public function ksefConnections(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/ksef/connections', array_merge([], $params), null, $options);
    }

    /** Create a fictitious company in KSeF TEST (random NIP) and connect it, to try KSeF sending end to end  (POST /einvoice/ksef/test-company) */
    public function ksefTestCompany(mixed $body = null, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/ksef/test-company', array_merge([], $params), $body, $options);
    }

    /** Start certificate sign-in: get a KSeF challenge and the unsigned AuthTokenRequest XML to sign externally  (POST /einvoice/ksef/auth/challenge) */
    public function ksefAuthChallenge(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/ksef/auth/challenge', array_merge([], $params), $body, $options);
    }

    /** Finish certificate sign-in: accept the signed AuthTokenRequest, enroll a KSeF Authentication certificate, store it encrypted  (POST /einvoice/ksef/auth/complete) */
    public function ksefAuthComplete(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/ksef/auth/complete', array_merge([], $params), $body, $options);
    }

    /** Revoke a KSeF connection: the stored KSeF token is deleted at once  (DELETE /einvoice/ksef/connections/{cid}) */
    public function ksefDisconnect(string|int|float|bool $cid, array $params = [], array $options = []): mixed
    {
        return $this->request('DELETE', '/einvoice/ksef/connections/' . rawurlencode((string) $cid), array_merge([], $params), null, $options);
    }

    /** KSeF status of a sent invoice: processing / accepted (KSeF number) / rejected (code and reason) (free)  (GET /einvoice/ksef/invoices/{sid}) */
    public function ksefInvoiceStatus(string|int|float|bool $sid, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/ksef/invoices/' . rawurlencode((string) $sid), array_merge([], $params), null, $options);
    }

    /** UPO (official KSeF receipt, XML signed by the Ministry of Finance) of an accepted invoice (free)  (GET /einvoice/ksef/invoices/{sid}/upo) */
    public function ksefInvoiceUpo(string|int|float|bool $sid, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/ksef/invoices/' . rawurlencode((string) $sid) . '/upo', array_merge([], $params), null, $options);
    }

    /** List invoices sent to KSeF under your API key (newest first, free)  (GET /einvoice/ksef/invoices) */
    public function ksefInvoices(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/ksef/invoices', array_merge([], $params), null, $options);
    }

    /** Liveness check  (GET /health) */
    public function health(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/health', array_merge([], $params), null, $options);
    }

    /** Look up a company in official EU business registers  (GET /company) */
    public function lookupCompany(string|int|float|bool $q, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/company', array_merge(['q' => $q], $params), null, $options);
    }

    /** Batch company-registry lookup (max 25 queries)  (POST /company/batch) */
    public function lookupCompanyBatch(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/company/batch', array_merge([], $params), $body, $options);
    }

    /** Screen a name against EU, UN, OFAC and UK sanctions lists  (GET /sanctions/screen) */
    public function screenSanctions(string|int|float|bool $name, array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/sanctions/screen', array_merge(['name' => $name], $params), null, $options);
    }

    /** Batch sanctions screening (max 50 names)  (POST /sanctions/screen/batch) */
    public function screenSanctionsBatch(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/sanctions/screen/batch', array_merge([], $params), $body, $options);
    }

    /** Validate an EU e-invoice (EN 16931, XRechnung; UBL, CII, ZUGFeRD/Factur-X PDF)  (POST /einvoice/validate) */
    public function validateEinvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/validate', array_merge([], $params), $body, $options);
    }

    /** Read an incoming EU e-invoice (UBL, CII, ZUGFeRD/Factur-X PDF) into JSON  (POST /einvoice/parse) */
    public function parseEinvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/parse', array_merge([], $params), $body, $options);
    }

    /** Create a valid EU e-invoice (UBL 2.1, CII, ZUGFeRD/Factur-X PDF) from simple JSON  (POST /einvoice/create) */
    public function createEinvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/create', array_merge([], $params), $body, $options);
    }

    /** Counterparty due-diligence report (VAT, register, sanctions, EORI, LEI, Peppol) with risk flags  (POST /ddreport) */
    public function ddReport(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/ddreport', array_merge([], $params), $body, $options);
    }

    /** Is this platform on France's official list of Plateformes Agréées (PA)? (free)  (GET /fr/pa-registry) */
    public function frPaRegistry(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/fr/pa-registry', array_merge([], $params), null, $options);
    }

    /** AI draft from a plain PDF invoice -> EN 16931 / XRechnung (for review)  (POST /einvoice/extract) */
    public function extractEinvoiceFromPdf(mixed $body = null, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/extract', array_merge([], $params), $body, $options);
    }

    /** SANDBOX: verify buyer -> create -> validate -> route -> transmit (mock or partner Peppol sandbox) -> signed evidence receipt  (POST /einvoice/send) */
    public function sendEinvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/send', array_merge([], $params), $body, $options);
    }

    /** Safe send: check the buyer and the invoice before sending (VAT, Peppol, sanctions, EN 16931) -> ok / warn / block  (POST /einvoice/preflight) */
    public function preflightEinvoice(mixed $body, array $params = [], array $options = []): mixed
    {
        return $this->request('POST', '/einvoice/preflight', array_merge([], $params), $body, $options);
    }

    /** Public key (JWK) for verifying /einvoice/send evidence receipts (free)  (GET /einvoice/evidence-key) */
    public function einvoiceEvidenceKey(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/evidence-key', array_merge([], $params), null, $options);
    }

    /** Aggregate e-invoice usage counts for the current UTC month (free; no PII)  (GET /einvoice/usage) */
    public function einvoiceUsage(array $params = [], array $options = []): mixed
    {
        return $this->request('GET', '/einvoice/usage', array_merge([], $params), null, $options);
    }

}
