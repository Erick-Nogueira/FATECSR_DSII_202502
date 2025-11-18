<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_usuario extends CI_Model
{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    4 - Usuario nao validado no sistema
    5 - Usuario desativado no sistema
    6 - Usuario nao cadastrado no sistema 
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Horário desativado no sistema
    10 - Horário já cadastrado
    11 - Horário não encontrado pelo método publico
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($nome, $email, $usuario, $senha)
    {
        try {
            //Verifico se o usuario ja esta cadastrado
            $retornoUsuario = $this->validaUsuario($usuario);

            if (
                $retornoUsuario['codigo'] == 4
            ) {
                //Query de inserção dos dados
                $this->db->query("insert into tbl_usuario (nome, email, usuario, senha)
                            values ('$nome', '$email', '$usuario', md5('$senha'))");

                //verificar se a inserção ocorreu com sucesso 
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'usuario cadastrado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de usuario.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoUsuario['codigo'],
                    'msg' => $retornoUsuario['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o  array $dados como as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function consultar($nome, $email, $usuario)
    {
        // função que servira para 3 tipos de consulta
        // para todos usuario
        // para um determinado usuario
        // para nomes de usuarios

        try {
            //Query para cosulta dados de acordo com parametros passados
            $sql = "select id_usuario, nome, usuario, email from tbl_usuario where estatus != 'D'";

            if (trim($nome) != '') {
                $sql = $sql . "and nome like '%$nome% ' ";
            }

            if (trim($email) != '') {
                $sql = $sql .  "and email = '$email' ";
            }

            if (trim($usuario) != '') {
                $sql = $sql .  "and usuario like '%$usuario%' ";
            }

            $retorno = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso 
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso. ',
                    'dados' => $retorno->result()
                );
            } else {
                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Dados nao encontrado.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com  as informaçoes tratadas 
        //Acima pela estrutura de decisao if
        return $dados;
    }

    public function alterar($idUsuario, $nome, $email, $senha)
    {
        try {
            //verifico se o professor ja esta cadastrado
            $retornoUsuario = $this->validaIdUsuario($idUsuario);

            if ($retornoUsuario['codigo'] == 1) {
                //inicio a query para atualicao 
                $query = "update tbl_usuario set ";

                //vamos comparar os itens 
                if ($nome !== '') {
                    $query .= "nome = '$nome', ";
                }

                if ($email !== '') {
                    $query .= "email = '$email', ";
                }

                if ($senha !== '') {
                    $query .= "senha = md5('$senha'), ";
                }

                //termino a concatenação da query
                $queryFinal = rtrim($query, ', ') . " where id_usuario = $idUsuario";

                //executo a query de atualização dos dados
                $this->db->query($queryFinal);

                //verificar se atualizaçao  ocorreu  com sucesso 
                if ($this->db->affected_rows() >= 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'usuario atualizado corretamente. '
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na atualização na tabela de usuario. '
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoUsuario['codigo'],
                    'msg' => $retornoUsuario['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENCAO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela  estrutura de decição if
        return $dados;
    }

    public function desativar($idUsuario)
    {
        try {
            //verifico se o usuario  ja esta cadastrado
            $retornoUsuario = $this->validaIdUsuario($idUsuario);

            if ($retornoUsuario['codigo'] == 1) {

                //Query de atualização dos dados
                $this->db->query("update tbl_usuario set estatus = 'D'
                              where id_usuario = $idUsuario");

                //Verificar se a atualizacao ocorreu com sucesso 
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'usuario Desativado Corretamente. '
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na desativação do usuario. '
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoUsuario['codigo'],
                    'msg' => $retornoUsuario['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima  pela estrutura de decição if
        return $dados;
    }

    private function validaUsuario($usuario)
    {
        try {
            //atributo retorno recebe o resultado do select
            //sem  status pois teremos que validar 
            //para verificar se esta deletado virtualmente ou nao 
            $retorno = $this->db->query("select * from tbl_usuario where usuario = '$usuario'");

            // Verifica se  a qauntidade de linhas trazidas na consulta e superior a 0
            // vinculamos o reqsultado da query para tratarmos o resultado do status    
            $linha = $retorno->row();

            if ($retorno->num_rows() == 0) {
                $dados = array(
                    'codigo' => 4,
                    'msg' => 'usuario nao existe no base de dados. '
                );
            } else {
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 5,
                        'msg' => 'Usuario desativado na base de dados nao pode ser utilizado'
                    );
                } else {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Usuario Correto'
                    );
                }
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        return $dados;
    }

    private function validaIdUsuario($idUsuario)
    {
        try {
            //atributo retorno recebe o resultado do select
            //sem  status pois teremos que validar 
            //para verificar se esta deletado virtualmente ou nao 
            $retorno = $this->db->query("select * from tbl_usuario where id_usuario = '$idUsuario'");

            // Verifica se  a qauntidade de linhas trazidas na consulta e superior a 0
            // vinculamos o reqsultado da query para tratarmos o resultado do status    
            $linha = $retorno->row();

            if ($retorno->num_rows() == 0) {
                $dados = array(
                    'codigo' => 4,
                    'msg' => 'usuario nao existe no base de dados. '
                );
            } else {
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 5,
                        'msg' => 'Usuario ja DESATIVADO NA BASE DE DADOS!'
                    );
                } else {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Usuario Correto'
                    );
                }
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        return $dados;
    }

    public function validalogin($usuario, $senha)
    {
        try {

            // Consulta usuário + senha
            $retorno = $this->db->query("
            SELECT * FROM tbl_usuario 
            WHERE usuario = '$usuario' 
            AND senha = md5('$senha')
        ");

            // Verifica se encontrou
            if ($retorno->num_rows() == 0) {
                return array(
                    'codigo' => 4,
                    'msg' => 'usuario ou senha invalidos.'
                );
            }

            // Pega a linha
            $linha = $retorno->row();

            // Verifica se está desativado
            if (trim($linha->estatus) == "D") {
                return array(
                    'codigo' => 5,
                    'msg' => 'Usuario ja DESATIVADO NA BASE DE DADOS!'
                );
            }

            // Login OK
            return array(
                'codigo' => 1,
                'msg' => 'Usuario Correto',
                'dados' => $linha
            );
        } catch (Exception $e) {
            return array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
    }
}
