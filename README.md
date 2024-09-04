
# Guia Completo: Criando e Executando uma API com Laravel e Docker

Este guia descreve o processo de criação e execução de uma API de municípios com **Laravel** utilizando **Docker**, e também explica a **Arquitetura de 3 Camadas** que usamos.

## Comandos Utilizados para Criar a API

1. **Criar o projeto Laravel**:
   ```bash
   composer create-project --prefer-dist laravel/laravel api-ibge "^9.0"
   ```

2. **Iniciar o Container do Docker**:
   Para subir o ambiente com o Docker:
   ```bash
   docker-compose up --build
   ```

3. **Correção de Problemas de Permissões no Laravel**:
   Se houver problemas de permissão (exemplo: logs não gravando ou cache não acessível), execute o seguinte comando:
   ```bash
   docker exec -it laravel_app bash
   chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
   chmod -R 775 /var/www/storage /var/www/bootstrap/cache
   ```

4. **Acessar o Container do Laravel**:
   Caso precise acessar o bash do container diretamente:
   ```bash
   docker exec -it laravel_app /bin/bash
   ```

5. **Reiniciar Containers**:
   Se precisar reiniciar os containers sem reconstruir:
   ```bash
   docker-compose up -d
   ```

6. **Remover Containers Específicos**:
   Para remover um container específico:
   ```bash
   docker rm -f <container_id>
   ```

7. **Desligar Todos os Containers**:
   Caso precise parar e remover todos os containers:
   ```bash
   docker-compose down
   ```

8. **Liberar Porta Ocuparada**:
   Se encontrar problemas de porta em uso, execute o seguinte comando para verificar e liberar a porta:
   ```bash
   sudo lsof -i :8000
   ```

---

## Arquitetura de 3 Camadas

Na nossa implementação, usamos a **Arquitetura de 3 Camadas** para organizar o código da API, separando responsabilidades de forma clara. As três camadas são:

1. **Controller (Apresentação)**:
    - Responsável por lidar com a entrada do usuário (requisições HTTP) e devolver a resposta apropriada. Não contém lógica de negócio.
    - No nosso exemplo, o **ApiMunicipiosController** recebe as requisições e delega a lógica para o **ApiMunicipiosManager**.

   ```php
   public function index(Request $request, $uf)
   {
       return $this->apiMuniciopiosManager->getMunicipios($request, $uf);
   }
   ```

2. **Manager (Lógica de Negócio)**:
    - Contém a lógica de negócio da aplicação. Ele coordena as interações com APIs externas, manipula os dados e decide o que deve ser retornado.
    - No nosso caso, o **ApiMunicipiosManager** faz a chamada para as APIs externas (BrasilAPI, IBGE) e aplica transformações nos dados antes de enviá-los de volta para o Controller.

   ```php
   public function getMunicipios(Request $request, $uf)
   {
       // Lógica para buscar e transformar dados
   }
   ```

3. **Transformers (Camada de Dados)**:
    - Essa camada é responsável por transformar os dados recebidos das APIs externas para o formato esperado pela aplicação ou pelo frontend.
    - Usamos o **MunicipioTransformer** para garantir que os dados tenham a estrutura correta antes de serem enviados ao cliente.

   ```php
   class MunicipioTransformer {
       public static function transform($municipio, $provider) {
           // Transforma os dados
       }
   }
   ```

---

## Resumo do Processo

1. **Criar o Projeto Laravel** com o Composer.
2. **Iniciar os Containers Docker** para rodar a aplicação.
3. **Corrigir Permissões** (se necessário).
4. **Reiniciar ou Remover Containers** conforme a necessidade.
5. **Consultar APIs Externas** com a arquitetura de 3 camadas para obter e transformar dados de municípios.

Agora, sua API está pronta para ser usada e pode ser acessada via os endpoints configurados no Laravel.

---
