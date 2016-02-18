# katFilters
WP masonry with ACF filters and infinite scroll
Difficultés: filtres avec masonry & infinite scroll au clic du bouton "more"

## Affichage des filtres

Objets de filtres créés via ACF

A chaque choix de filtres, la masonry est recréée via une requête ajax. J'ai opté pour des liens plutôt que des formulaires par facilité

Le conteneur des filtres est placé en dehors du conteneur de la masonry
      <div class="filters"></div>
Le contenu de .filters est ajouté dans le conteneur .masonry (.filters-content), ce qui permet de l'updater à chaque choix de filtres: il est chargé en ajax avec la masonry.

Chaque filtre est appelé dans un array, avec divers paramètres:
les paramètres par défaut sont name et type
<pre>
$filter1 = array(
              'name'     =>'activity_place',
              'type'     =>'choice'
            );
</pre>

Les paramètres pour les ranges comportent en plus un label et un "data-hour" permettant de spécifier si le range contient une valeur décimale ou une valeur de durée
<pre>
$filter3 = array(
              'name'      =>'people_num',
              'type'      =>'rangeSlider',
              'label'     => __('Number of players', 'site'),
              'data-hour' =>'false'
            );</pre>

## Affichage masonry

A chaque choix de filtres, la masonry est recréée via une requête ajax. J'ai opté pour des liens plutôt que des formulaires par facilité, excepté le champ de recherche.

Les requêtes sont faites avec des meta_queries en fonction des paramètres GET, meta_queries qui sont ajoutées si ces paramètres existent. Il a fallu faire une exception pour :
- les GET de type 'page', 'paged' (pour être certain, en cas de bug), 
- les valeurs de GET de type "all" => pas besoin de meta_query
- les GET de type 'filter_search', qui est celui du  filtre de recherche, un peu particulier
- le 'GET' de type 'post_type', qui n'est pas une metaquery (je l'ai mis par défaut pour être certaine qu'il n'y ait pas de bug)

Noter que lors d'un range, on exécute deux metaquery sur une seule des valeurs (exemple: case 'people_num_min' génère deux metaqueries, case 'people_num_max' n'en génère pas).

Filtre de recherche: j'ai dû faire un petit hack sur la query et ajouter un filtre de recherche du terme dans le contenu et le titre via add_filter( 'posts_where', 'wp_posts_where', 10, 2 );
la fonction wp_posts_where est dans acf-filter-search.php. Le champ de recherche basique de WP ne fonctionnait pas.

## Pagination avec infinitescroll

Pagination custom pour éviter les bugs (fonction navi_custom dans acf-filter-search.php)

Le conteneur de la pagination est placé en dehors du conteneur de la masonry
      <div class="infinite-pagenavi-container"></div>
Le contenu de .infinite-pagenavi-container est ajouté dans le conteneur .masonry (.infinite-pagenavi), ce qui permet de l'updater à chaque choix de filtres: il est chargé en ajax avec la masonry.
=> obligation de le faire comme ça pour que l'infinite scroll repère la nouvelle nav.

## Réutiliser cette app:

Fichiers: voir dans le theme urbangaming
CSS: _isotope.scss
PHP: external/acf-filter-search.php, tpl-activities.php
JS: js/site-masonry.js, js/jquery.ba-bbq.min.js, js/jquery.lazyload.js, js/imagesloaded.pkgd.min.js, js/isotope.pkgd.min.js, js/jquery.nstSlider.js, js/list.min.js
optionnel: js/pronamic-hack.js

Plugins: 
ACF Pro
Optionnel: pronamic

Créer les champs ACF, et updater les noms des filtres ainsi que leurs paramètres dans le template (tpl-activities.php) pour qu'ils correspondent aux noms des champs. Changer également le post_type dans $args.
S'il y a des ranges, appeler le 1er champ [nom]_min, et le deuxième champ [nom]_max. NE PAS OUBLIER CES SUFFIXES.


Brol que je n'ai pas pu résoudre: lors d'un choix de filtres, infinitescroll reprend ses argus par défaut; j'ai donc dû un peu le réécrire.

## Attention: en cas d'utilisation avec la carte

- Utiliser pronamic
- Nommer les champs code postal "cp" et ville "town".
- ajouter js/pronamic-hack.js
- Virer les lignes suivantes:

* acf-filter-search.php (69 à 73):

<pre>$place = get_field('activity_place');
$type = get_field('activity_type');
$target = get_field('target');
         
$icons = '</pre>```html<ul class="masonry__icons-list"><li><span class="masonry-icon masonry-icon--'. $place . '"></span></li><li><span class="masonry-icon masonry-icon--'. $type . '"></span></li><li><span class="masonry-icon masonry-icon--'. $target . '"></span></li></ul>```<pre>';</pre>
            
* acf-filter-search.php (210 à 219):
<pre>
  <?php 
          $place = get_field('activity_place');
          $type = get_field('activity_type');
          $target = get_field('target');
        ?>

```html
        <ul class="masonry__icons-list">
          <li><span class="masonry-icon masonry-icon--activity_place masonry-icon--<?php echo $place;?>"></span></li>
          <li><span class="masonry-icon masonry-icon--activity_type masonry-icon--<?php echo $type;?>"></span></li>
          <li><span class="masonry-icon masonry-icon--activity_target masonry-icon--<?php echo $target;?>"></span></li>
        </ul>
        ```</pre>
