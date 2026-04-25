<div align="center">
  <a href="#portugues"><img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/4x3/br.svg" width="20"> Português</a> | <a href="#english"><img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/4x3/us.svg" width="20"> English</a>
</div>

---

<h2 id="portugues"><img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/4x3/br.svg" width="25"> Versão em Português</h2>

# Dextemidos - Portal e Sistema de Gestão (Atlética Computação UVA)

Este é o sistema oficial da Atlética de Computação da Universidade Veiga de Almeida (UVA), a Dextemidos. O projeto integra um portal público para engajamento estudantil e um Dashboard Administrativo para gestão interna.

## Sobre o Projeto

O portal centraliza a comunicação da Atlética, permitindo que os alunos acompanhem confrontos esportivos, adquiram produtos na loja oficial e conheçam a diretoria. O backend foi desenvolvido em PHP para alimentar o frontend dinamicamente via API interna conectada a um banco de dados MySQL.

## Ferramentas e Tecnologias Utilizadas

## Ferramentas e Tecnologias Utilizadas

* **Front-end:** HTML5 <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" width="18">, CSS3 <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" width="18"> e JavaScript Vanilla <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" width="18">.
* **Back-end:** PHP 8.x <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="18"> com arquitetura orientada a segurança.
* **Banco de Dados:** MySQL <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg" width="18"> integrado via PDO (PHP Data Objects).
* **Servidor e Segurança:** Apache <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/apache/apache-original.svg" width="18"> com diretivas via `.htaccess`.

## Funcionalidades Principais

### 🌐 Portal Público
* **Gestão de Confrontos:** Visualização dinâmica de jogos e resultados por modalidade.
* **Loja Oficial:** Vitrine de produtos com galeria de imagens e integração de links de venda.
* **Membros e Equipe:** Apresentação da diretoria organizada por categorias hierárquicas.
* **Eventos:** Listagem de festas e encontros com status de vendas em tempo real.
* **Contato:** Formulário com validação e processamento via SMTP.

### 🪪 Painel Administrativo
* **Autenticação:** Sistema de login com proteção de sessão e criptografia de senhas.
* **Controle de Dados (CRUD):** Gerenciamento completo de confrontos, produtos, membros e eventos.
* **Integridade:** Lógica implementada para prevenção de duplicidade de dados em operações de banco (Anti-F5).

## 🔐 Segurança e Boas Práticas

Como foco em robustez e integridade, o projeto implementa:
* **Prevenção de SQL Injection:** Uso exclusivo de Prepared Statements com PDO em todas as queries.
* **Segurança de Servidor:** Configurações no `.htaccess` incluindo:
    * `X-Frame-Options "SAMEORIGIN"` contra Clickjacking.
    * `X-Content-Type-Options "nosniff"` contra ataques de MIME Sniffing.
    * `Content-Security-Policy` para controle de fontes de recursos seguras.
* **Isolamento de Credenciais:** Separação de dados sensíveis em arquivo de configuração não versionado.

## Licença

© 2026 Rodrigo Farah / Atlética Computação UVA.
Todos os direitos reservados. É proibida a cópia ou reprodução deste código sem autorização prévia.

<br>
<hr>
<br>

<h2 id="english"><img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/4x3/us.svg" width="25"> English Version</h2>

# Dextemidos - Portal & Management System (UVA Computing Athletics)

This is the official system for the Computing Athletics department of Veiga de Almeida University (UVA), known as Dextemidos. The project features a public portal for student engagement and an Administrative Dashboard for internal management.

## About the Project

The portal centralizes Athletics communication, allowing students to track sports matches, purchase official merchandise, and meet the board members. The backend was developed in PHP to dynamically feed the frontend through an internal API connected to a MySQL database.

## Tech Stack

## Tech Stack

* **Front-end:** HTML5 <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" width="18">, CSS3 <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" width="18">, and Vanilla JavaScript <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" width="18">.
* **Back-end:** PHP 8.x <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="18"> focused on security architecture.
* **Database:** MySQL <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg" width="18"> integrated via PDO (PHP Data Objects).
* **Server & Security:** Apache <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/apache/apache-original.svg" width="18"> with configurations via `.htaccess`.

## Core Features

### 🌐 Public Portal
* **Match Management:** Dynamic display of games and results categorized by sport.
* **Official Store:** Product showcase with image galleries and sales link integration.
* **Members & Team:** Board member presentation organized by hierarchical categories.
* **Events:** Listing of parties and meetups with real-time sales status.
* **Contact:** Validated form processing via SMTP.

### 🪪 Admin Panel
* **Authentication:** Login system with session protection and password encryption.
* **Data Control (CRUD):** Full management of matches, products, members, and events.
* **Integrity:** Logic implemented to prevent data duplication in database operations (Anti-F5).

## 🔐 Security & Best Practices

Focused on robustness and integrity, the project implements:
* **SQL Injection Prevention:** Exclusive use of Prepared Statements with PDO in all queries.
* **Server Security:** `.htaccess` configuration including:
    * `X-Frame-Options "SAMEORIGIN"` against Clickjacking.
    * `X-Content-Type-Options "nosniff"` against MIME Sniffing attacks.
    * `Content-Security-Policy` to control secure resource sources.
* **Credential Isolation:** Separation of sensitive data into a non-versioned configuration file.

## 📜 License

© 2026 Rodrigo Farah / UVA Computing Athletics.
All rights reserved. Unauthorized copying or reproduction of this code is prohibited.