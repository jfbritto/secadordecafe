<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }

    public function termos(): View
    {
        $body = <<<'HTML'
<h2>1. Aceitação dos termos</h2>
<p>Ao criar uma conta na Roça Nossa ("Plataforma"), você concorda com estes Termos de Uso. Se não concordar, não utilize o serviço.</p>

<h2>2. Descrição do serviço</h2>
<p>A Plataforma oferece ferramentas online para gestão da roça: cadastro de produtores, controle de saldos, secagens, despesas, equipe e relatórios. Nasceu na cafeicultura e expande para outras culturas conforme o produtor demanda.</p>

<h2>3. Conta de usuário</h2>
<p>Você é responsável por manter a confidencialidade da sua senha e por todas as atividades realizadas na sua conta. Notifique-nos imediatamente em caso de uso não autorizado.</p>

<h2>4. Acesso à plataforma</h2>
<p>Estamos em fase inicial e o acesso à Roça Nossa é gratuito por tempo indeterminado. Quando definirmos a estrutura de planos e cobrança, você será comunicado por e-mail com antecedência mínima de 30 dias antes de qualquer mudança.</p>

<h2>5. Cancelamento</h2>
<p>Você pode encerrar sua conta a qualquer momento. Seus dados podem ser exportados antes do encerramento.</p>

<h2>6. Propriedade dos dados</h2>
<p>Os dados inseridos na Plataforma pertencem a você. Disponibilizamos exportação dos seus dados a qualquer tempo, mediante solicitação.</p>

<h2>7. Limitação de responsabilidade</h2>
<p>O serviço é fornecido "como está". Não nos responsabilizamos por perdas decorrentes de uso indevido, falhas de conexão à internet do usuário ou força maior.</p>

<h2>8. Alterações</h2>
<p>Podemos atualizar estes termos. Comunicaremos alterações relevantes por e-mail. O uso continuado após as alterações implica aceitação.</p>

<h2>9. Contato</h2>
<p>Dúvidas: <a class="text-leaf-700 font-semibold hover:underline" href="mailto:contato@rocanossa.com.br">contato@rocanossa.com.br</a></p>
HTML;

        return view('pages.legal', ['title' => 'Termos de uso', 'body' => $body]);
    }

    public function privacidade(): View
    {
        $body = <<<'HTML'
<h2>1. Quais dados coletamos</h2>
<p>Coletamos: nome, e-mail, telefone, dados da roça (nome, cidade, estado) e dados de uso (logs de acesso, atividades realizadas).</p>

<h2>2. Como usamos seus dados</h2>
<p>Os dados são usados para prestar o serviço (gestão da roça), suporte e melhorias do produto. Nunca vendemos ou compartilhamos com terceiros para fins de marketing.</p>

<h2>3. Onde armazenamos</h2>
<p>Dados ficam em servidores no Brasil, com backups diários criptografados. Conexão sempre via HTTPS.</p>

<h2>4. Seus direitos (LGPD)</h2>
<p>Você pode solicitar a qualquer momento: acesso aos seus dados, correção, exportação ou exclusão. Atendemos em até 15 dias úteis. A roça é sua, os dados também.</p>

<h2>5. Cookies</h2>
<p>Usamos apenas cookies essenciais para autenticação e segurança. Não usamos cookies de rastreamento de terceiros.</p>

<h2>6. Subprocessadores</h2>
<p>Provedor de e-mail transacional e hospedagem em VPS. Todos sob acordo de confidencialidade e LGPD.</p>

<h2>7. Contato do encarregado (DPO)</h2>
<p><a class="text-leaf-700 font-semibold hover:underline" href="mailto:privacidade@rocanossa.com.br">privacidade@rocanossa.com.br</a></p>
HTML;

        return view('pages.legal', ['title' => 'Política de privacidade', 'body' => $body]);
    }
}
