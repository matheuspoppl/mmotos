
<?php

class Permissoes extends model {
    
    public function __construct($id = "") {
        parent::__construct(); 
    }
        
    public function getPermissoes($id_empresa,$id_grupopermissao){
        
        $array = array();
        if(!empty($id_empresa) && !empty($id_grupopermissao)){
           //busca os id dos parametros correspondentes do grupo de permissao pesquisado 
           $sql = "SELECT pg.parametros FROM permissoes_grupos as pg WHERE pg.id = '$id_grupopermissao' AND pg.id_empresa = '$id_empresa' AND pg.situacao = 'ativo'";
           $sql = $this->db->query($sql);
           if($sql->rowCount() > 0){
                $pr = $sql->fetch();
                //caso não tenha nenhum parametro, é setado como 0 para não dar erro na pesquisa sequente
                if(empty($pr["parametros"])){
                    $pr["parametros"] = 0;
                }
                
                //busca os nomes dos parametros de permissão do grupo de permissao pesquisado
                $sqlA = "SELECT pp.nome FROM permissoes_parametros as pp WHERE pp.id_empresa = '$id_empresa' AND pp.id IN (".$pr["parametros"].")";
                $sqlA = $this->db->query($sqlA);
                if($sqlA->rowCount()>0){
                    foreach ($sqlA->fetchAll() as $item){
                        $array[] = $item["nome"];
                    } 
                }
           }
        }
        return $array;
    }

    public function pegarListaPermissoes($empresa){
       $array = array();
       
       $sql = "SELECT pp.id, pp.nome FROM permissoes_parametros as pp WHERE pp.id_empresa = '$empresa'";      
       $sql = $this->db->query($sql);
       if($sql->rowCount()>0){
         $array = $sql->fetchAll(); 
       }
       return $array;
    }
    
    public function pegarListaGrupos($empresa) {
       $array = array();
       
       $sql = "SELECT pg.id, pg.nome FROM permissoes_grupos as pg WHERE pg.id_empresa = '$empresa' AND pg.situacao = 'ativo' ORDER BY id DESC";      
       $sql = $this->db->query($sql);
       if($sql->rowCount()>0){
         $array = $sql->fetchAll(); 
       }
       return $array; 
    }

    public function adicionarGrupo($nome,$permLista,$empresa){
        
        if(!empty($nome) && !empty($permLista) && !empty($empresa)){
            
            $params = implode(",", $permLista);
            $p = new Permissoes();
            $ipcliente = $p->pegaIPcliente();

            $alteracoes = ucwords($_SESSION["nomeFuncionario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - CADASTRO";
            $sql = "INSERT INTO permissoes_grupos (id,id_empresa,nome,parametros,alteracoes,situacao) VALUES (DEFAULT, '$empresa', '$nome', '$params','$alteracoes', 'ativo')";
            $this->db->query($sql); 
        }           
    }

    public function pegarPermissoesAtivas($id_grupo,$empresa){
       $array = array();
       if(!empty($id_grupo) && !empty($empresa)){         
            $sql = "SELECT * FROM permissoes_grupos WHERE id_empresa = '$empresa' AND id = '$id_grupo' AND situacao = 'ativo'";
            $sql = $this->db->query($sql);
                
            if($sql->rowCount() > 0){
                $array = $sql->fetch();
                $array["params"] = explode(",", $array["parametros"]);
            }
        }
        return $array;
    }  
    
    public function editarGrupo($pnome,$pLista,$palter,$id_grupo,$empresa){
        if(!empty($pnome) && !empty($pLista) && !empty($palter) && !empty($id_grupo) && !empty($empresa)){
            $params = implode(",", $pLista);
            $p = new Permissoes();
            $ipcliente = $p->pegaIPcliente();
            $palter = $palter." | ".ucwords($_SESSION["nomeFuncionario"])." - ".$ipcliente." - ".date('d/m/Y H:i:s')." - ALTERACAO";
            $sql = "UPDATE permissoes_grupos SET nome = '$pnome', parametros = '$params', alteracoes = '$palter' WHERE id = '$id_grupo' AND id_empresa = '$empresa'";
            $this->db->query($sql);             
        }
    }

    public function excluirGrupo($id_grupo,$empresa){
        if(!empty($id_grupo) && !empty($empresa)){
            
            $numFunc = 0;
            $f =  new Funcionarios();
            $numFunc = $f->qtdFuncionariosGrupo($id_grupo,$empresa);
            if($numFunc == 0){
                
                //se não achar nenhum usuario associado ao grupo - pode deletar, ou seja, tornar o cadastro situacao=excluído
                $sql = "SELECT alteracoes FROM permissoes_grupos WHERE id = '$id_grupo' AND id_empresa = '$empresa' AND situacao = 'ativo'";
                $sql = $this->db->query($sql);
                
                if($sql->rowCount() > 0){ 
                   $sql = $sql->fetch(); 
                   $palter = $sql["alteracoes"];
                   $p = new Permissoes();
                   $ipcliente = $p->pegaIPcliente();
                   $palter = $palter." | ".ucwords($_SESSION["nomeFuncionario"])." - ".$ipcliente." - ".date('d/m/Y H:i:s')." - EXCLUSAO";
                   $sqlA = "UPDATE permissoes_grupos SET alteracoes = '$palter', situacao = 'excluido' WHERE id = '$id_grupo' AND id_empresa = '$empresa'";
                   $this->db->query($sqlA);  
                }         
            }
            return $numFunc; 
        }
    }
    
    public function pegaIPcliente(){
//        $http_client_ip       = $_SERVER['HTTP_CLIENT_IP'];
//        $http_x_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote_addr          = $_SERVER['REMOTE_ADDR'];
        $ip = '000.00.00.00';
        /* VERIFICO SE O IP REALMENTE EXISTE NA INTERNET */
//        if(!empty($http_client_ip)){
//            $ip = $http_client_ip;
//            /* VERIFICO SE O ACESSO PARTIU DE UM SERVIDOR PROXY */
//        } elseif(!empty($http_x_forwarded_for)){
//            $ip = $http_x_forwarded_for;
//        } else {
            /* CASO EU NÃO ENCONTRE NAS DUAS OUTRAS MANEIRAS, RECUPERO DA FORMA TRADICIONAL */
            $ip = $remote_addr;
//        }
        return $ip ;
    }

}
