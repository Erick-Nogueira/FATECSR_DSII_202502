<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_professor extends CI_Model {
  /*
  Validar dos tipos  de retornos nas validações (codigo de erro)
  0 - Erro de exceção
  1 -  Operação realizada  no banco de dados com sucesso (Inserção, alteração, consulta, Exclução)
  8 - Houve um problema de inserção, alteração, consulta, exclução
  9 - Professor desativado no sistema
  10 - Professor ja cadastrado
  11 - Professora nao encontrado pelo metodo publico
  12 - Metodo auxiliar de consulta que nao trouxe dados
  */  

  public function inserir($nome, $cpf, $tipo){
    try{
        //Verifico se o professor ja esta cadastrado
        $retornoConsulta = $this->consultaProfessorCpf($cpf);

        if($retornoConsulta['codigo'] != 9 &&
        $retornoConsulta['codigo'] != 10){
            //Query de inserção dos dados
            $this->db->query("insert into tbl_professor (nome, tipo, cpf)
                            values ('$nome', '$tipo', '$cpf')");
        
        //verificar se a inserção ocorreu com sucesso 
        if($this->db->affected_rows() > 0){
            $dados = array('codigo' => 1,
                            'msg' => 'Professor cadastrado corretamente.');
        }else{
            $dados = array('codigo' => 8,
                           'msg' => 'Houve algum problema na inserção na tabela de professor.');
        }
    }else{
       $dados = array('codigo' => $retornoConsulta['codigo'],
                      'msg' => $retornoConsulta['msg']);
    }
    }catch (Exception $e){
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o  array $dados como as informações tratadas
    //acima pela estrutura de decisao if
    return $dados;
  }


  private function consultaProfessorCpf($cpf){
    try{
        //Query para consultar dados de acordo com parametros passados
        $sql = "select * from tbl_professor where cpf = '$cpf'";

        $retornoProfessor = $this->db->query($sql);

        //verificar  se a consulta ocorreu com sucesso 
        if($retornoProfessor->num_rows() > 0){
            $linha = $retornoProfessor->row();
            if (trim($linha->estatus) == "D") {
                $dados = array(
                    'codigo' => 9,
                    'msg'=> 'Professor desativado no sistema, caso precise reativar a mesma, fale com administrador.'
                );
            }else{
                $dados = array('codigo' => 10,
                                'msg' => 'Professor ja cadastrado no sistema.');
            }
        }else{
            $dados = array('codigo' => 98,
                           'msg' => 'Professor nao encontrado.');
        }
    }catch (Exception $e){
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENCAO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o array o $dados  com as informação tratadas
    //acima  pela estrutura de decição if
    return $dados;
  }

  private function consultaProfessorCod($codigo) {
    try{
        //Query para consultar dados de acordo com parametros passados
        $sql = "select * from tbl_professor where codigo = '$codigo' and estatus = ''";

        $retornoProfessor = $this->db->query($sql);

        //verifico se a consulta ocorreu  com sucesso
        if($retornoProfessor->num_rows() > 0){
            $dados = array('codigo' => 1,
                           'msg' => 'Consulta efetuada com sucesso.');
        
        }else{
            $dados= array('codigo' => 98, 
                          'msg' => 'Professor nao encontrado.');
        }
    } catch (Exception $e){
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o array $dados com as informações tratadas
    //acima pela estrutura de decisao if
    return $dados;
  }

  public function consultar($codigo, $nome, $cpf, $tipo){
    try{
        //Query para cosulta dados de acordo com parametros passados
        $sql = "select * from tbl_professor where estatus = '' ";
        
        if(trim($codigo) != '') {
            $sql .= "and codigo = '$codigo' ";
        }

        if(trim($cpf) != '') {
            $sql .= "and cpf = '$cpf' ";
        }

        if(trim($nome) != '') {
            $sql .= "and nome like '%$nome%' ";
        }

        if(trim($tipo) != '') {
            $sql .= "and tipo = '$tipo' ";
        }

        $sql .= "order by nome ";

        $retorno = $this->db->query($sql);

        //verificar se a consulta ocorreu com sucesso 
        if($retorno->num_rows() > 0){
            $dados =array('codigo' => 1,
                          'msg' => 'Consulta efetuada com sucesso. ',
                          'dados' => $retorno->result());
        
        }else{
            $dados = array('codigo' => 11,
                           'msg' => 'Professor nao encontrado.');
        }
    }catch (Exception $e) {
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o array $dados com  as informaçoes tratadas 
    //Acima pela estrutura de decisao if
    return $dados; 
  }
  
  public function alterar($codigo, $nome, $cpf, $tipo){
    try{
        //verifico se o professor ja esta cadastrado
        $retornoConsulta = $this->consultaProfessorCod($codigo);

        if($retornoConsulta['codigo'] == 1){
            //inicio a query para atualicao 
            $query = "update tbl_professor set ";

            //vamos comparar os itens 
            if($nome !== ''){
                $query .= "nome = '$nome', ";
            }

            if($cpf !== ''){
                $query .= "cpf = '$cpf', ";
            }

            if($tipo !== ''){
                $query .= "tipo = '$tipo', ";
            }

            //termino a concatenação da query
            $queryFinal = rtrim($query, ', ') . " where codigo = $codigo";

            //executo a query de atualização dos dados
            $this->db->query($queryFinal);

            //verificar se atualizaçao  ocorreu  com sucesso 
            if($this->db->affected_rows() > 0){
                $dados = array('codigo' => 1,
                               'msg' => 'Professor atualizado corretamente. ');

            }else{
                $dados = array('codigo' => 8,
                               'msg' => 'Houve algum problema na atualização na tabela de professores. ');
            }
        }else{
            $dados = array('codigo' => $retornoConsulta['codigo'],
                           'msg' => $retornoConsulta['msg']);
        }

    }catch (Exception $e) {
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENCAO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o array $dados com as informações tratadas
    //acima pela  estrutura de decição if
    return $dados;
  }

  public function desativar($codigo){
    try{
        //verifico se o professor  ja esta cadastrado
        $retornoConsulta = $this->consultaProfessorCod($codigo);

        if($retornoConsulta['codigo'] == 1){

            //Query de atualização dos dados
            $this->db->query("update tbl_professor set estatus = 'D'
                              where codigo = $codigo");

            //Verificar se a atualizacao ocorreu com sucesso 
            if($this->db->affected_rows() > 0) {
                $dados = array('codigo' => 1,
                               'msg' => 'Professor Desativado Corretamente. ');
            
            }else {
                $dados = array('codigo' => 8,
                               'msg' => 'Houve algum problema na desativação do professor. ');
            }
        
        } else {
            $dados = array('codigo' => $retornoConsulta['codigo'],
                           'msg' => $retornoConsulta['msg']);
        }
    } catch (Exception $e){
        $dados = array(
            'codigo' => 0,
            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
        );
    }
    //Envia o array $dados com as informações tratadas
    //acima  pela estrutura de decição if
    return $dados;
  }


  
}
?>
