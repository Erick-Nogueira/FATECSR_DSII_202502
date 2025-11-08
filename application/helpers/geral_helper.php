<?php
defined('BASEPATH') or exit('No direct script access allowed');

   /*
   Função para verificar os parâmetros vindos do FrontEnd
   */
    function verificarParam($atributos, $lista) {
        //1º   -Verificar se os elementos do Front estão nos atributos necessários
        foreach($lista as $key => $value) {
            if(array_key_exists($key, get_object_vars($atributos))){
                $estatus = 1;
            }else {
                $estatus = 0;
                break;
            }
        }

        // 2º   -Verificando a quantidade de elementos
        if(count(get_object_vars($atributos)) != count($lista)) {
            $estatus = 0;
        }

        return $estatus;
    }



    function validarDados($valor, $tipo, $tamanhoZero = true) {

        //verifica vazio ou nulo
        if(is_null($valor) || $valor === '') {
            return array('codigoHelper' => 2, 'msg' => 'Conteudo nulo ou vazio.');
        }
        // Se considerar '0' como vazio
        if ($tamanhoZero && ($valor === 0 || $valor === '0')) {
            return array('codigoHelper' => 3, 'msg' => 'Conteudo zerado.');
        }

        switch ($tipo) {
            case 'int':
                // Filtro como inteiro, aceita '123' ou 123
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                    return array('codigoHelper' => 4, 'msg' => 'Conteudo não inteiro.');
                }
                break;
            Case 'string': 
            //garante queé string não vazia após trim
            if (!is_string($valor) || trim($valor) === '') {
                return array('codigoHelper' => 5, 'msg' => 'Conteudo não é um texto.');
            } 
            break;
        case 'date':
            //Verifico se tem padrão de data
            if(!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)){
                return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
            }else{
                //tenta criar DataTime no formato Y-m-d
                $d = DateTime::createFromFormat('Y-m-d', $valor);
                if(($d->format('Y-m-d') === $valor) == false ){
                    return array('codigoHelper' => 6, 'msg' => 'Data inválido.');
                }
            }
            break;
        case 'hora':
            //Verifico se tem padrão de hora
            if(!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)){
                return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido. ');
            }
            break;
        default:
            return array('codigoHelper' => 0, 'msg' => 'Tipo de dado não definido. ');
        }
        // Valor default da variável $retorno caso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validação correta.');;
     }


     /*
     Função para verificar os tipos de dados para consulta
     */
    function validarDadosConsulta($valor, $tipo) {

        if ($valor != ''){
            switch ($tipo) {
                case 'int':
                    //Filtra como inteiro, aceita '123' ou 123
                    if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                        return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro. ');
                    }
                    break;
                case 'string':
                    //Garante que é string não vazia após trim
                    if(!is_string($valor) || trim($valor) === ''){
                        return array('codigoHelper' => 5, 'msg' => 'Conteúdo não é um texto. ');
                    } 
                    break;
                case 'date':
                    //verifico se tem padrão de data
                    if(!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)){
                        return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido. ');
                    }else{
                        // Tenta criar DataTime no formato Y-m-d
                        $d = DateTime::createFromFormat('Y-m-d', $valor);
                        if(($d->format('Y-m-d') === $valor) == false  ){
                            return array('codigoHelper' => 6, 'msg' => 'Data inválida. ');
                        }
                    }
                    break;
                case 'hora':
                    //Verifico se tem padrão de hora
                    if(!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)){
                        return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.'); 
                    }
                    break;
                default:
                    return array('codigoHelper' => 97, 'msg' => 'Tipo de dado não definido. ');
                    
            }
        }
        //Valor default da varável $retorno aso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validação correta. ');;
    }
    /*
    Função para verificar se datas ou horários iniciais são maiores
    entre eles
    */
    function compararDataHora($valorInicial, $valorFinal, $tipo) {
        //Passamos a string para hora
        $valorInicial = strtotime($valorInicial);
        $valorFinal = strtotime($valorFinal);
        
        if ($valorInicial != '' && $valorFinal != ''){
            if ($valorInicial > $valorFinal) {
                switch ($tipo) {
                case 'hora':
                    return array('codigoHelper' => 13, 'msg' => 'Hora Final menor que a Hora Inicial.');
                    break;
                case 'data':
                    return array('codigoHelper' => 14, 'msg' => 'Data Final menor que a Data Inicial.');
                    break;
                default:
                    return array('codigoHelper' => 97, 'msg' => 'Tipo de verificação não definida.');    
                }
            }
        }

        // Valor default da variável $retorno caso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validação correta.');
    }

    /*
    Função para verificar se o CPF é valido quanto a sua estrutura
    */
    Function validarCPF($cpf) {
        // Remove tudo que não for número
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // CPF deve ter 11 digítos
        if (strlen($cpf) != 11) {
            return array('codigoHelper' => 15, 'msg' => 'CPF com menos de 11 dígitos.');
        }

        // Rejeita CPFs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return array('codigoHelper' => 16, 'msg' => 'CPF com todos dígitos iguais.');
        }

        // Calcula os dígitos verificadores
        for ($t =9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = (10 * $soma) % 11;
            $digito = ($digito == 10) ? 0 : $digito;

            if ($cpf[$t] != $digito) {
                return array('codigoHelper' => 17, 'msg' => 'CPF com dígitos verificadores incorretos.');
            }
        }

        return array('codigoHelper' => 0, 'msg' => 'CPF válido.');
    }
?>

