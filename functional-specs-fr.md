# Nulengeo

Nulengeo est un jeu de quiz en géographie. Le but du jeu est de placer géographiquement des villes sur une carte de France sans aide, ni repère.

## Fonctionnement

A partir d'une carte de France interactive (zoomable, déplaçable) dépouillée de toute information (aucun nom de ville, département, lieu, route, rivière, parc, montagne, etc), seule
la topographie reste (altitude, relief, cours d'eau, biome), le joueur doit y placer des villes en placant un marqueur au plus proche.

Le jeu propose une série de 10 villes, dont le choix est déterminé par le mode de jeu (voir [Modes de jeu](#modes-de-jeu)). Le marqueur peut être repositionné librement avant validation
explicite du placement (bouton de confirmation) : aucune pénalité pour un clic accidentel.

Pour chaque ville, le joueur voit uniquement son nom et sa population (voir [Sélection des villes](#sélection-des-villes)) — aucune autre indication (région, département) n'est donnée.
Aucune limite de temps n'est imposée pour placer un marqueur.

Une fois le marqueur validé, le jeu révèle la position réelle de la ville, la distance d'erreur (en km) et le score obtenu pour ce placement (voir [Score](#score)), avant de passer
à la ville suivante. La carte revient à une vue centrée sur la France entière au début de chaque nouvelle ville (aucun état de zoom/déplacement n'est conservé d'une ville à l'autre).

Ce score s'additionne à un total de fin de partie.

## Modes de jeu

Le jeu propose 3 modes de difficulté, disponibles librement dès le départ (pas de déblocage progressif). Chaque mode détermine exclusivement dans quel palier de population
(voir [Sélection des villes](#sélection-des-villes)) sont tirées les 10 villes de la partie :

 - **Facile** : villes du palier Grande uniquement (> 100 000 habitants)
 - **Moyen** : villes du palier Moyenne uniquement (20 000 à 100 000 habitants)
 - **Difficile** : villes du palier Petite uniquement (1 000 à 20 000 habitants)

## Sélection des villes

Les villes sont tirées aléatoirement, sans remise (une même ville ne peut pas apparaître deux fois dans une même partie), parmi les communes de France métropolitaine
issues du Code Officiel Géographique (COG) de l'INSEE, avec population et coordonnées du centroïde de la commune.

Seules les communes de plus de 1 000 habitants sont éligibles, quel que soit le mode, afin d'exclure les hameaux trop peu peuplés pour offrir une signature topographique
exploitable.

Les communes éligibles sont réparties selon un unique critère, le niveau de population, en 3 paliers à seuils fixes :

 - **Petite** : de 1 000 à 20 000 habitants
 - **Moyenne** : de 20 000 à 100 000 habitants
 - **Grande** : plus de 100 000 habitants

Le critère de niveau de superficie initialement envisagé est abandonné : il n'est pas retenu pour la sélection des villes.

## Score

A chaque placement, le score est calculé à partir de la distance à vol d'oiseau entre le marqueur posé par le joueur et le centroïde de la commune, selon une décroissance
exponentielle :

```
score = 1000 * e^(-ln(2) / 25 * distance_km)
```

 - score maximum par ville : 1000 points (distance nulle)
 - la distance de calibration est de 25 km : au-delà de cette erreur, le score obtenu tombe à environ la moitié du score maximum (500 points)
 - aucun score plancher garanti : le score tend vers 0 à mesure que la distance augmente, sans borne minimale

Le score total d'une partie est la somme des scores obtenus sur les 10 villes, soit un maximum de 10 000 points.

## Fin de partie

En fin de partie, un écran de résumé affiche le score total et le détail par ville (distance, score). Pour cette première version, aucune donnée n'est persistée :
pas de compte joueur, pas de classement, pas de sauvegarde de meilleur score.

## Plateforme et langue

Le jeu cible en priorité les navigateurs desktop (interactions souris/clavier) ; le support tactile/mobile est prévu pour une itération ultérieure.

Les contenus textuels de l'interface sont structurés pour l'internationalisation (i18n) dès cette première version, bien que seul le français soit fourni initialement.

## Hors périmètre de cette spécification fonctionnelle

Le choix technique du fond de carte (source des tuiles, rendu de la topographie dépouillée de toute information) est renvoyé aux spécifications techniques.
