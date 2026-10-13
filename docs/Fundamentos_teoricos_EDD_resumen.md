# 1.1 Fundamentos teóricos

El Sistema EDD administra convocatorias, expedientes y evaluaciones del Programa de Estímulos al Desempeño del Personal Docente. Su desarrollo se sustenta en cuatro fundamentos que justifican la organización de la información, la revisión de evidencias y la distribución de responsabilidades.

## 1.1.1 Evaluación docente basada en evidencias

La evaluación del desempeño docente requiere criterios definidos y evidencias que permitan valorar las actividades académicas. Los lineamientos del TecNM establecen criterios de evaluación y responsabilidades de las comisiones. Este fundamento justifica que el sistema relacione los documentos de cada docente con una convocatoria y registre puntuaciones, observaciones y resultados mediante rúbricas.<span style="font-size: 10pt;"><em> “Tecnológico Nacional de México, 2025, capítulos IV y V”</em></span>

El flujo Local, Estatal y Nacional implementado en el proyecto organiza revisiones sucesivas y conserva sus resultados. Esta organización es una decisión funcional del sistema; no se atribuye a los lineamientos citados.

## 1.1.2 Bases de datos relacionales

El modelo relacional organiza la información en tablas vinculadas mediante claves. Las relaciones y restricciones de integridad permiten mantener asociaciones coherentes entre los registros. En el proyecto, este enfoque vincula usuarios, instituciones, convocatorias, solicitudes, documentos y evaluaciones. Así, cada evidencia puede consultarse dentro del expediente correspondiente y cada revisión se relaciona con su evaluador.<span style="font-size: 10pt;"><em> “IBM, s. f.-a”</em></span>

## 1.1.3 Arquitectura web y separación de responsabilidades

La arquitectura de tres capas distingue presentación, lógica de aplicación y almacenamiento de datos. Esta separación facilita mantener cada responsabilidad y modificar componentes con menor dependencia entre ellos. En el Sistema EDD, la interfaz presenta formularios y resultados, el servidor valida las reglas del proceso y la base de datos conserva los registros. Se trata de una separación lógica, que no exige tres servidores físicos.<span style="font-size: 10pt;"><em> “IBM, s. f.-b”</em></span>

## 1.1.4 Control de acceso por roles y ámbito

El control de acceso basado en roles asocia permisos con las responsabilidades de los usuarios. La autorización también debe comprobarse para cada recurso y acción solicitada. Este principio fundamenta los perfiles de administrador, evaluador y docente, así como las restricciones por institución, estado y nivel de evaluación. Por ejemplo, un evaluador local debe acceder a los expedientes asignados de su plantel; ocultar botones no sustituye la validación en el servidor.<span style="font-size: 10pt;"><em> “OWASP Foundation, s. f.”</em></span>

## Referencias

<span style="font-size: 10pt;"><em>“Tecnológico Nacional de México. (2025). Lineamientos para la operación del Programa de Estímulos al Desempeño del Personal Docente para los Institutos Tecnológicos Federales y Centros. https://edd.tecnm.mx/formatos/2025/LINEAMIENTOS_EDD_2025.pdf”</em></span>

<span style="font-size: 10pt;"><em>“IBM. (s. f.-a). What is a relational database? https://www.ibm.com/think/topics/relational-databases”</em></span>

<span style="font-size: 10pt;"><em>“IBM. (s. f.-b). What is three-tier architecture? https://www.ibm.com/think/topics/three-tier-architecture”</em></span>

<span style="font-size: 10pt;"><em>“OWASP Foundation. (s. f.). Authorization cheat sheet. OWASP Cheat Sheet Series. https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html”</em></span>

Fuentes consultadas el 12 de octubre de 2026.
