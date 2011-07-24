<?xml version="1.0" encoding="UTF-8" ?>
<Stock>
{foreach $autos as $auto}
 <Vehicule>
  <CodePVO>{$auto._code|escape}</CodePVO>
  <Nom>{$auto._title|escape}</Nom>
  <Societe_marque>{$auto._marque|escape}</Societe_marque>
  <Adresse>{$auto._adresse|escape}</Adresse>
  <AdresseSuite>{$auto._adresse2|escape}</AdresseSuite>
  <Cpostal>{$auto._zipcode}</Cpostal>
  <Ville>{$auto._city|escape}</Ville>
  <Telephone>{$auto._phone|escape}</Telephone>
  <Telephone2/>
  <Email>{$auto._mail}</Email>
  <Contact>{$auto.__nom|escape}</Contact>
  <Numpoli>{$auto.ref}</Numpoli>
  <Statut>ST</Statut>
  <Annee>{$auto.annee}</Annee>
  <Date1Mec>{$auto.mec}</Date1Mec>
  <Genre>{$auto.type}</Genre>
  <Marque><![CDATA[{$auto.marque|escape}]]></Marque>
  <Famille><![CDATA[{$auto.modele|escape}]]></Famille>
  <Version><![CDATA[{$auto.version|escape}]]></Version>
  <Modele><![CDATA[{$auto.modele|escape} {$auto.version|escape}]]></Modele>
  <Type></Type>
  <Energie>{$auto.energie}</Energie>
  <Puissance>{$auto.pf}</Puissance>
  <PuissanceReelle>{$auto.pr}</PuissanceReelle>
  <Cylindree></Cylindree>
  <NbPlaces>{$auto.place}</NbPlaces>
  <NbPortes>{$auto.porte}</NbPortes>
  <Km>{$auto.km}</Km>
  <KmGaranti></KmGaranti>
  <Couleur><![CDATA[{$auto.couleur}]]></Couleur>
  <Boite>{$auto.vitesse}</Boite>
  <NbRapports>{$auto.rapport}</NbRapports>
  <PvTTC>{$auto.prix}</PvTTC>
  <PremiereMain>{if $auto.main == 1}VRAI{else}FAUX{/if}</PremiereMain>
  <Garantie><![CDATA[{$auto.garantie|escape}]]></Garantie>
  <Categorie>{$auto.cat}</Categorie>
  <Equipements><![CDATA[{$auto.option|escape}]]></Equipements>
  <Site><![CDATA[{$auto._title|escape}]]></Site>
  <Lieu><![CDATA[{$auto._city|escape}]]></Lieu>
  <Photos>{$auto.photo}</Photos>
 </Vehicule>
{/foreach}
</Stock>