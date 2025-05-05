<?php

namespace App\Http\Controllers;

use \App\Models\LinkPagamentoGerencianet;
use \App\Models\GerencianetConfig;
use Illuminate\Http\Request;
use Gerencianet\Gerencianet;
use Gerencianet\Exception\GerencianetException;
use Illuminate\Support\Facades\Mail;
use Redirect;
use Auth;
use Session;
use Exception;

class LinkPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $link_pagamento = LinkPagamentoGerencianet::where('r_auth', Auth::user()->id)->orderBy('id', 'DESC')->get();

        return view('link_pagamento.index', [
            'link_pagamento' => $link_pagamento
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('link_pagamento.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $store = $request->all();

        $sendmail = false;

        if (isset($store['sendmail'])) {
            $sendmail = true;
        }

        unset($store['sendmail']);

        if (!env('APP_GERENCIANET_SANDBOX_ID')) {
            return back()->withInput()->withErrors('A Chave da API do Gerencianet não foi encontrada!');
        }

        try {

            $options = GerencianetConfig::options();

            $api = new Gerencianet($options);

            $price = str_replace([',', '.'], '', $store['price']);

            $item = [
                'name' => $store['product'],
                'value' => (int)($price)
            ];

            $items =  [
                $item
            ];

            $metadata = array('notification_url' => env('APP_URL') . '/link_pagamento/confirm');

            $body  =  [
                'items' => $items,
                'metadata' => $metadata
            ];

            $charge = $api->createCharge([], $body);

            $params = [
                'id' => $charge['data']['charge_id']
            ];

            if (isset($store['due_date']) && !empty($store['due_date'])) {

                $due_date = explode('/', $store['due_date']);

                $due_date = $due_date[2] . '-' . $due_date[1] . '-' . $due_date[0];

            }
            else
            {
                $due_date = date('Y-m-d', strtotime("+3 day", strtotime(date('Y-m-d'))));
            }

            if ($due_date < date('Y-m-d')) {
                return back()->withInput()->withErrors('Data do vencimento não pode ser menor que a data atual');
            }

            $expire_at = $due_date;

            $body = [
                'message' => $store['product'],
                'expire_at' => $expire_at,
                'request_delivery_address' => false,
                'payment_method' => 'all'
            ];

            $response = $api->linkCharge($params, $body);

            $r_link_pagamento = new LinkPagamentoGerencianet();

            $r_link_pagamento->payment_link = $response['data']['payment_url'];
            $r_link_pagamento->email = $store['email'];
            $r_link_pagamento->product = $store['product'];
            $r_link_pagamento->gerencianet_charge_id = $response['data']['charge_id'];
            $r_link_pagamento->price = $store['price'];
            $r_link_pagamento->obs = $store['obs'];
            $r_link_pagamento->due_date = $store['due_date'];
            $r_link_pagamento->r_auth = $user->id;
            $r_link_pagamento->status = 1;

            $r_link_pagamento->save();

            if ($sendmail) {
                Mail::to($r_link_pagamento->email)->send( new \App\Mail\NovoLinkPagamento( $r_link_pagamento ) );
            }

            Session::flash('flash_success', "Link de pagamento criado com sucesso");

        } catch (Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
        catch (GerencianetException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return Redirect::to('/link_pagamento');
    }
}
