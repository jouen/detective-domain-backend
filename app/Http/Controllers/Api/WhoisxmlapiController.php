<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WhoisxmlapiController extends Controller
{
    public function checkSiteData(Request $request) {

        // validate required parameter
        $validator = Validator::make($request->all(), [
            'data_type' => 'required|in:domain,contact',
            'host_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->messages(), 
            ], 422);
        }

        // create request to get domain data on whoisxmlapi.com
        $request_url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey=' . env('WHOISXMLAPI_KEY') . '&domainName=' . $request->host_name;
        $response = Http::get($request_url);

        //get xml body and convert to json object
        $xml_body = $response->getBody();
        $xml_body = simplexml_load_string($xml_body);
        $json_data = json_encode($xml_body);
        $json_data = json_decode($json_data);

        //check response if there is error on the request
        if (property_exists($json_data, 'errorCode')) {
            return response()->json([
                'status' => false,
                'message' => $json_data->msg,
            ], 422);
        }

        //format the response depends on what data_type user want
        $hostname_data = [];
        if ($request->data_type === 'domain') {
            //domain information format
            $hostname_data = $this->getDomainInformation($json_data);
        } else if ($request->data_type === 'contact') {
            //contact information format
            $hostname_data = $this->getContactInformation($json_data);
        }

        return response()->json([
            'status' => true,
            'data' => $hostname_data,
        ], 200);
    }

    private function getDomainInformation($json_data) {
        $domain_info = [];

        if (property_exists($json_data, 'domainName')) {
            $domain_info['domainName'] = $json_data->domainName;
        }
        if (property_exists($json_data, 'registrarName')) {
            $domain_info['registrar'] = $json_data->registrarName;
        }
        if (property_exists($json_data, 'registryData')) {
            $domain_info['registration_date'] = \Carbon\Carbon::parse($json_data->registryData->createdDate)->format('d/m/Y');
            $domain_info['expiration_date'] = \Carbon\Carbon::parse($json_data->registryData->expiresDate)->format('d/m/Y');
        }
        if (property_exists($json_data, 'estimatedDomainAge')) {
            $domain_info['estimated_domain_age'] = $json_data->estimatedDomainAge;
        }
        if (property_exists($json_data, 'nameServers')) {
            $host_names = implode(', ', $json_data->nameServers->hostNames->Address);
            $domain_info['host_names'] = (strlen($host_names) > 25) ? substr($host_names,0,25).'...' : $host_names;
        }

        return $domain_info;
    }

    private function getContactInformation($json_data) {
        $contact_info = [];

        if (property_exists($json_data, 'registrant')) {
            $contact_info['registrant_name'] = $json_data->registrant->name;
        }
        if (property_exists($json_data, 'technicalContact')) {
            $contact_info['technical_contact_name'] = $json_data->technicalContact->name;
        }
        if (property_exists($json_data, 'administrativeContact')) {
            $contact_info['administrative_contact_name'] = $json_data->administrativeContact->name;
        }
        if (property_exists($json_data, 'contactEmail')) {
            $contact_info['contact_email'] = $json_data->contactEmail;
        }

        return $contact_info;
    }
}