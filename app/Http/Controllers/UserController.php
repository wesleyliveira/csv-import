<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //Listar Usuários
    public function index()
    
    {

        //Recuperar os Registros do Banco de Dados 
        $users = User::get();


        //Carregar a VIEW
        return view('users.index', ['users' => $users]);
        
    }

    public function import(Request $request)
    {
        //Validar Arquivo
        $request->validate([
            'file'=>'required|mimes:csv,txt|max:2048',
        ],[
            'file.required'=>'O campo arquivo é obrigatório.',
            'file.mimes'=>'Arquivo Inválido, necessário enviar arquivo do tipo CSV.',
            'file.max'=>'Tamanho do arquivo excede :max Mb.'
        ]);
        
        //Criar o array com as colunas do banco de dados
        $headers=['name','email','password'];

        //Receber o arquivo, ler os dados e converter string em array
        $datafile=array_map('str_getcsv',file($request->file('file')));

        //Váriavel para receber registros
        $numberRegisteredRecords=0;

        //Váriavel email
        $emailAlreadyRegistered = false;
        

        //Percorrer as linhas do CSV
        foreach($datafile as $keyData=>$row){

            //Converter linha em array
            $values=explode(';',$row[0]);

            //Percorrer as colunas do cabeçalho
            foreach($headers as $key => $header){

                //Atribuir o valor ao elemento do array
                $arrayvalues[$keyData][$header]=$values[$key];



                if($header =="email"){
                    //Verificar se a coluna é email
                    if(User::where('email',$arrayvalues[$keyData]['email'])->first()){

                        //Atribuir email na lista de ja cadastrados
                        $emailAlreadyRegistered .= $arrayvalues[$keyData]['email'] . ",";
                    }
                }
                    
                    //Verificar se a coluna é senha
                    if($header=="password"){

                        //Criptografar senha
                        // $arrayvalues[$keyData][$header] = Hash::make($arrayvalues[$keyData]['password'],['rounds' => 12]);

                        //Gerando senha aleatória
                        $arrayvalues[$keyData][$header] = Hash::make(Str::random(7),['rounds' => 12]);
                        
                    }
 
                }
            
            //Incrementar mais um registro na quantidade de registros que serão cadastrados
            $numberRegisteredRecords++;     
 
        }

        // Verificar se a email cadastrados
        if($emailAlreadyRegistered){
            return back()->with('error','Dados não importados. <br>Existem emails cadastrados: ' . $emailAlreadyRegistered);
        }

        //Cadastrar registros no banco de dados

         User::insert($arrayvalues);

         //Redirecionando usuário para página anterior e mensagem de sucesso
         return back()->with('sucess','Dados importados com sucesso. <br>Quantidade: ' . $numberRegisteredRecords);

    }

}
