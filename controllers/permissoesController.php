<?php
class permissoesController extends controller{
    public function __construct() {
        parent::__construct();
    
       $func = new Funcionarios();
       
       //verifica se está logado
       if($func->isLogged() == false){
           header("Location: ".BASE_URL."/login"); 
       }
       //verifica se tem permissão para ver esse módulo
       if(in_array("permissoes_ver",$_SESSION["permissoesFuncionario"]) == FALSE){
           header("Location: ".BASE_URL."/home"); 
       }
    }
     
    public function index() {
        $dados = array();
        $dados['infoFunc'] = $_SESSION;
        
        $p = new Permissoes();
        $dados["listaGrupoPermissoes"]  = $p->pegarListaGrupos($_SESSION["idEmpresaFuncionario"]);
        $this->loadTemplate("permissoes",$dados);      
    } 
    
    public function adicionar_grupo() {
        
        if(in_array("permissoes_add",$_SESSION["permissoesFuncionario"]) == FALSE){
            header("Location: ".BASE_URL."/permissoes"); 
        }
        
        $array = array();
        $dados['infoFunc'] = $_SESSION;
        $p = new Permissoes();
           
        if(isset($_POST["nome"]) && !empty($_POST["nome"]) && isset($_POST["permissoes"]) && !empty($_POST["permissoes"])){
             
             $pnome = addslashes($_POST["nome"]);
             $pLista = $_POST["permissoes"];
             $p->adicionarGrupo($pnome,$pLista,$_SESSION["idEmpresaFuncionario"]);
             header("Location: ".BASE_URL."/permissoes");
        }else{

             $dados["listaPermissoes"] = $p->pegarListaPermissoes($_SESSION["idEmpresaFuncionario"]);
             $this->loadTemplate("permissoes-addGrupo",$dados);
        }  
    }
    
    public function editarGrupo($id) {
        if(in_array("permissoes_edt",$_SESSION["permissoesFuncionario"]) == FALSE || empty($id) || !isset($id)){
            header("Location: ".BASE_URL."/permissoes"); 
        }
        $array = array();
        $dados['infoFunc'] = $_SESSION;
        $p = new Permissoes();
        
        $id = addslashes($id);
        if(isset($_POST["enome"]) && !empty($_POST["enome"]) && isset($_POST["epermissoes"]) && !empty($_POST["epermissoes"])){
            
            $pnome = addslashes($_POST["enome"]);
            $pLista = $_POST["epermissoes"];
            $palter = addslashes($_POST["alter"]);
            $p->editarGrupo($pnome,$pLista,$palter,$id,$_SESSION["idEmpresaFuncionario"]);
            header("Location: ".BASE_URL."/permissoes");
        }else{
            
            $dados["listaPermissoes"] = $p->pegarListaPermissoes($_SESSION["idEmpresaFuncionario"]);
            $dados["permAtivas"] = $p->pegarPermissoesAtivas($id,$_SESSION["idEmpresaFuncionario"]);
            $this->loadTemplate("permissoes-editGrupo",$dados);
        } 

    }
      

    public function excluirGrupo($id) {
        if(in_array("permissoes_exc",$_SESSION["permissoesFuncionario"]) == FALSE || empty($id) || !isset($id)){
            header("Location: ".BASE_URL."/permissoes"); 
        }
        
        $p = new Permissoes();
        $id_grupo = addslashes($id);
        $numFunc = $p->excluirGrupo($id_grupo,$_SESSION["idEmpresaFuncionario"]);  
        
        $dados = array();
        $dados["aviso"] = "";
        if($numFunc != 0){
            $dados["aviso"] = "Nenhum funcionário pode estar associado ao grupo para ocorrer a exclusão.<br/>".$numFunc." funcionário(s) está(ão) associado(s) ao grupo.";
        }
                
        $dados['infoFunc'] = $_SESSION;       
        $dados["listaGrupoPermissoes"]  = $p->pegarListaGrupos($_SESSION["idEmpresaFuncionario"]);
        $this->loadTemplate("permissoes",$dados);  
      }
    }   
  
?>

