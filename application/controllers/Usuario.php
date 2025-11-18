<?php
defined('BASEPATH') or exit('No direct script access allowed');



class Usuario extends CI_Controller
{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    8 - E-mail em formato invalido
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Prâmetros passados do front não correspondem ao método
    */

    //Atributos privados da classe
    private $idUsuario;
    private $nome;
    private $email;
    private $usuario;
    private $senha;

    //Geters dos atributos
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    //Setters dos atributos
    public function setIdUsuario($idUsuarioFront)
    {
        $this->idUsuario = $idUsuarioFront;
    }

    public function setNome($nomeFront)
    {
        $this->nome = $nomeFront;
    }

    public function setEmail($emailFront)
    {
        $this->email = $emailFront;
    }

    public function setUsuario($usuarioFront)
    {
        $this->usuario = $usuarioFront;
    }

    public function setSenha($senhaFront)
    {
        $this->senha = $senhaFront;
    }

    public function inserir()
    {
        //Atributos  para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "nome" => '0',
                "email" => '0',
                "usuario" => '0',
                "senha" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd. '];
            } else {
                //Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoNome = validarDados($resultado->nome, 'string', true);
                $retornoEmail = validarDados($resultado->email, 'email', true);
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha = validarDados($resultado->senha, 'string', true);

                if ($retornoNome['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoNome['codigoHelper'],
                        'campo' => 'Nome',
                        'msg' => $retornoNome['msg']
                    ];
                }

                if ($retornoEmail['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoEmail['codigoHelper'],
                        'campo' => 'E-mail',
                        'msg' => $retornoEmail['msg']
                    ];
                }

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo' => 'Usuário',
                        'msg' => $retornoUsuario['msg']
                    ];
                }

                if ($retornoSenha['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoSenha['codigoHelper'],
                        'campo' => 'Data Inicio',
                        'msg' => $retornoSenha['msg']
                    ];
                }
                //Se não encontrar erros
                if (empty($erros)) {
                    //Fazendo os setters
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->inserir(
                        $this->getNome(),
                        $this->getEmail(),
                        $this->getUsuario(),
                        $this->getSenha()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg' => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inseperado: ' . $e->getMessage()];
        }


        //Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // tranforma o array em JSON
        echo json_encode($retorno);
    }

    public function consultar()
    {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "nome" => '0',
                "email" => '0',
                "usuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no
                                                              FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoNome = validarDadosConsulta($resultado->nome, 'string');
                $retornoEmail = validarDadosConsulta($resultado->email, 'email');
                $retornoUsuario = validarDadosConsulta($resultado->usuario, 'string');

                if ($retornoNome['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoNome['codigoHelper'],
                        'campo' => 'Nome',
                        'msg' => $retornoNome['msg']
                    ];
                }

                if ($retornoEmail['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoEmail['codigoHelper'],
                        'campo' => 'E-mail',
                        'msg' => $retornoEmail['msg']
                    ];
                }

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo' => 'Usuário',
                        'msg' => $retornoUsuario['msg']
                    ];
                }

                //Se não encontrar erros 
                if (empty($erros)) {
                    //Fazendo os setters
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->consultar(
                        $this->getNome(),
                        $this->getEmail(),
                        $this->getUsuario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        //captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu: ' .
                    $e->getMessage()
            );
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg'],
                'dados' => $resBanco['dados']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Tranforma o array em JSON
        echo json_encode($retorno);
    }

    public function alterar()
    {
        //Atributos para controlar o status de nosso método
        $erro = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "idUsuario" => '0',
                "nome" => '0',
                'email' => '0',
                "senha" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = [
                    'codigo' => 99,
                    'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];
            } else {
                //Pelo menos um dos três parâmetros prescisam ter dados pára acontecer a atualização
                if (
                    trim($resultado->nome) == '' && trim($resultado->email) == '' &&
                    trim($resultado->senha) == ''
                ) {
                    $erros[] = [
                        'codigo' => 12,
                        'msg' => 'Pelo menos em parâmetro prescisa ser passado para atualização'
                    ];
                } else {
                    //Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoIdUsuario = validarDados($resultado->idUsuario, 'int');
                    $retornoNome = validarDadosConsulta($resultado->nome, 'string');
                    $retornoEmail = validarDadosConsulta($resultado->email, 'string');
                    $retornoSenha = validarDadosConsulta($resultado->senha, 'string');

                    if ($retornoIdUsuario['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoIdUsuario['codigoHelper'],
                            'campo' => 'ID Usuario',
                            'msg' => $retornoIdUsuario['msg']
                        ];
                    }

                    if ($retornoNome['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoNome['codigoHelper'],
                            'campo' => 'Nome',
                            'msg' => $retornoNome['msg']
                        ];
                    }

                    if ($retornoEmail['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoEmail['codigoHelper'],
                            'campo' => 'E-mail',
                            'msg' => $retornoEmail['msg']
                        ];
                    }

                    if ($retornoSenha['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoSenha['codigoHelper'],
                            'campo' => 'Senha',
                            'msg' => $retornoSenha['msg']
                        ];
                    }

                    //Se não encontrar erros
                    if (empty($erros)) {
                        $this->setIdUsuario($resultado->idUsuario);
                        $this->setNome($resultado->nome);
                        $this->setEmail($resultado->email);
                        $this->setSenha($resultado->senha);

                        $this->load->model('M_usuario');

                        $resBanco = $this->M_usuario->alterar(
                            $this->getIdUsuario(),
                            $this->getNome(),
                            $this->getEmail(),
                            $this->getSenha()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            // Captura erro do banco
                            $erross[] = [
                                'codigo' => $resBanco['codigo'],
                                'msg'    => $resBanco['msg']
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $retorno =  array('codigo' => 0, 'msg' => 'ATENÇÃO: o seguinte erro aconteceu: ' . $e->getMessage());
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    public function desativar()
    {
        $erros = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "idUsuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = [
                    'codigo' => 99,
                    'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];
            } else {

                $retornoIdUsuario = validarDados($resultado->idUsuario, 'int');

                if ($retornoIdUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoIdUsuario['codigoHelper'],
                        'campo'  => 'ID Usuario',
                        'msg'    => $retornoIdUsuario['msg']
                    ];
                }

                if (empty($erros)) {

                    $this->setIdUsuario($resultado->idUsuario);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->desativar($this->getIdUsuario());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = [
                'codigo' => 0,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu: ' . $e->getMessage()
            ];
        }

        // Retorno Final
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => true,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = [
                'sucesso' => false,
                'erros'   => $erros
            ];
        }

        echo json_encode($retorno);
    }

    public function logar()
    {
        $erros = [];
        $sucesso = false;

        try {
            // Recebe JSON
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = [
                "usuario" => '0',
                "senha"   => '0'
            ];

            // Verifica se campos existem
            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = [
                    'codigo' => 99,
                    'msg'    => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];
            } else {
                // Valida os dados
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha   = validarDados($resultado->senha, 'string', true);

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo'  => 'Usuário',
                        'msg'    => $retornoUsuario['msg']
                    ];
                }

                if ($retornoSenha['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoSenha['codigoHelper'],
                        'campo'  => 'Senha',
                        'msg'    => $retornoSenha['msg']
                    ];
                }

                // Se não houver erros
                if (empty($erros)) {
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->validalogin(
                        $this->getUsuario(),
                        $this->getSenha()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $retorno = [
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu: ' . $e->getMessage()
            ];
        }

        // Retorno final
        if ($sucesso) {
            $retorno = [
                'sucesso' => true,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        } else {
            $retorno = [
                'sucesso' => false,
                'erros' => $erros
            ];
        }

        echo json_encode($retorno);
    }
}
