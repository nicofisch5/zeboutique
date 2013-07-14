<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Zeboutique
 * @package     Zeboutique_Starnet
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Starnet
 *
 * @category    Zeboutique
 * @package     Zeboutique_Starnet
 * @author      Zeboutique
 */
class Zeboutique_Starnet_Model_Import_Product extends Zeboutique_Zcore_Model_Import_Product_Abstract
{
    
    const IMPORT_PRODUCT_FILE = 'base_produits-starnet.csv';

    protected $_supplierId = 311;
    protected $_prefix = 'starnet';


    /**
     * Constructeur
     *
     * @return void
     */
    public function __construct()
    {
        // Mock that avoid dispatching
        $io = new Varien_Io_File();
        //$info   = pathinfo(self::FILENAME);
        $io->open(array('path' => Mage::getBaseDir().'/var/import'));
        $io->streamOpen(self::IMPORT_PRODUCT_FILE, 'r');

        // Skip headers
        $io->streamReadCsv();

        $this->_setFile($io);
    }
    
    /**
     * Try to update Magento SKU with starnet csv file
     * 
     * @return null
     */
    protected function _prepareData()
    {
        // Format Starnet
        // "R�f�rence compl�te";"R�f�rence";"Marque";"Couleur";"Code-barres";"D�signation";"Quantit� disponible";"Date de cr�ation";"Titre cat�gorie";"Tarif 1 HT";"Code douane";"Poids";"Hauteur";"Longueur";"Conditionnement";"D�signation Anglais";"D�signation Espagnol";"D�signation Allemand";"D�signation Italien";"Categorie Anglais";"Categorie Espagnol";"Categorie Allemand";"Categorie Italien";"Sous categorie Fran�ais";"Sous categorie Anglais";"Sous categorie Espagnol";"Sous categorie Allemand";"Sous categorie Italien";"Taille";"Matiere";"Photo 1";"Photo 2";"Photo 3";"Photo 4";"Photo 5";"Photo 6";"Photo 7";"EAN";"SKU";"Etat du produit";"Pointure";"Parfum";"Contenance";"Divers";"Description Fran�ais";"Description Anglais";"Description Espagnol";"Description Allemand";"Description Italien";"PVTTC Conseill�";"Remise d�s 3 pi�ces";"Remise d�s 6 pi�ces";"Remise d�s 12 pi�ces"

        $io = $this->_getFile();

        try {
            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $this->_prdData[] = array(
                    $csvLine[1],
                    $csvLine[0],
                    $csvLine[5],
                    $csvLine[44],
                    $csvLine[2],
                    $csvLine[6],
                    $csvLine[9],
                    $csvLine[8],
                    $csvLine[11],
                    array($csvLine[30],$csvLine[31],$csvLine[32],$csvLine[33],$csvLine[34],$csvLine[35],$csvLine[36]), // images
                    array(
                        'taille' => $csvLine[28],
                        'couleur' => $csvLine[3]
                    )// attributs
                );
            }
        } catch (Mage_Core_Exception $e) {
            $io->streamClose();
            echo 'Erreur : '.$e->getMessage();
        } catch (Exception $e) {
            $io->streamClose();
            Mage::logException($e);
            echo 'Erreur : '.$e->getMessage();
        }

        return $this;
    }
}