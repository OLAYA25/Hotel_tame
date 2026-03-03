<?php
/**
 * SimpleSpreadsheet - Clase ligera para generar archivos XLSX y ODS sin dependencias externas
 * Compatible con PHP 7.4+
 */
class SimpleSpreadsheet {
    private $format = 'xlsx';
    private $sheets = [];
    private $metadata = [];
    private $current_sheet = 0;
    
    public function __construct($format = 'xlsx') {
        $this->format = in_array($format, ['xlsx', 'ods']) ? $format : 'xlsx';
    }
    
    public function setMetadata($metadata) {
        $this->metadata = array_merge([
            'title' => 'Spreadsheet',
            'subject' => 'Generated Document',
            'creator' => 'SimpleSpreadsheet',
            'description' => '',
            'created' => date('Y-m-d H:i:s')
        ], $metadata);
    }
    
    public function addSheet($name) {
        $this->sheets[] = [
            'name' => $name,
            'data' => [],
            'styles' => [],
            'column_widths' => []
        ];
        $this->current_sheet = count($this->sheets) - 1;
        return $this;
    }
    
    public function addRow($data, $style = []) {
        if (!isset($this->sheets[$this->current_sheet])) {
            $this->addSheet('Sheet1');
        }
        
        $this->sheets[$this->current_sheet]['data'][] = $data;
        $this->sheets[$this->current_sheet]['styles'][] = $style;
        return $this;
    }
    
    public function setColumnWidth($column, $width) {
        if (isset($this->sheets[$this->current_sheet])) {
            $this->sheets[$this->current_sheet]['column_widths'][$column] = $width;
        }
        return $this;
    }
    
    public function saveToString() {
        switch ($this->format) {
            case 'xlsx':
                return $this->generateXLSX();
            case 'ods':
                return $this->generateODS();
            default:
                throw new Exception("Formato no soportado: {$this->format}");
        }
    }
    
    public function saveToFile($filename) {
        file_put_contents($filename, $this->saveToString());
        return $filename;
    }
    
    private function generateXLSX() {
        // Crear estructura ZIP para XLSX
        $zip = new ZipArchive();
        $tmp_file = tempnam(sys_get_temp_dir(), 'xlsx_');
        
        if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('No se pudo crear el archivo ZIP temporal');
        }
        
        // Agregar archivos necesarios para XLSX
        $zip->addFromString('[Content_Types].xml', $this->getXLSXContentTypes());
        $zip->addFromString('_rels/.rels', $this->getXLSXRelationships());
        $zip->addFromString('xl/workbook.xml', $this->getXLSXWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getXLSXWorkbookRels());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->getXLSXWorksheet());
        $zip->addFromString('docProps/app.xml', $this->getXLSXAppProps());
        $zip->addFromString('docProps/core.xml', $this->getXLSXCoreProps());
        
        $zip->close();
        
        $content = file_get_contents($tmp_file);
        unlink($tmp_file);
        
        return $content;
    }
    
    private function generateODS() {
        // Crear estructura ZIP para ODS
        $zip = new ZipArchive();
        $tmp_file = tempnam(sys_get_temp_dir(), 'ods_');
        
        if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('No se pudo crear el archivo ZIP temporal');
        }
        
        // Agregar archivos necesarios para ODS
        $zip->addFromString('content.xml', $this->getODSContent());
        $zip->addFromString('meta.xml', $this->getODSMeta());
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.spreadsheet');
        $zip->addFromString('META-INF/manifest.xml', $this->getODSManifest());
        
        $zip->close();
        
        $content = file_get_contents($tmp_file);
        unlink($tmp_file);
        
        return $content;
    }
    
    private function getXLSXContentTypes() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
</Types>';
    }
    
    private function getXLSXRelationships() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
    }
    
    private function getXLSXWorkbook() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheets>
        <sheet name="' . htmlspecialchars($this->sheets[0]['name'] ?? 'Sheet1') . '" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>
    </sheets>
</workbook>';
    }
    
    private function getXLSXWorkbookRels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>';
    }
    
    private function getXLSXWorksheet() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>';
        
        if (isset($this->sheets[0])) {
            foreach ($this->sheets[0]['data'] as $row_idx => $row) {
                $xml .= '<row r="' . ($row_idx + 1) . '">';
                foreach ($row as $col_idx => $cell) {
                    $cell_ref = $this->getCellReference($col_idx, $row_idx);
                    $xml .= '<c r="' . $cell_ref . '" t="inlineStr">';
                    $xml .= '<is><t>' . htmlspecialchars((string)$cell) . '</t></is>';
                    $xml .= '</c>';
                }
                $xml .= '</row>';
            }
        }
        
        $xml .= '</sheetData>
</worksheet>';
        
        return $xml;
    }
    
    private function getXLSXAppProps() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
    <Application>' . htmlspecialchars($this->metadata['creator']) . '</Application>
    <DocSecurity>0</DocSecurity>
    <ScaleCrop>false</ScaleCrop>
    <SharedDoc>false</SharedDoc>
    <HyperlinksChanged>false</HyperlinksChanged>
    <AppVersion>1.0</AppVersion>
</Properties>';
    }
    
    private function getXLSXCoreProps() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" 
                   xmlns:dc="http://purl.org/dc/elements/1.1/" 
                   xmlns:dcterms="http://purl.org/dc/terms/" 
                   xmlns:dcmitype="http://purl.org/dc/dcmitype/">
    <dc:title>' . htmlspecialchars($this->metadata['title']) . '</dc:title>
    <dc:subject>' . htmlspecialchars($this->metadata['subject']) . '</dc:subject>
    <dc:creator>' . htmlspecialchars($this->metadata['creator']) . '</dc:creator>
    <dcterms:created xsi:type="dcterms:W3CDTF" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $this->metadata['created'] . '</dcterms:created>
    <dc:description>' . htmlspecialchars($this->metadata['description']) . '</dc:description>
</cp:coreProperties>';
    }
    
    private function getODSContent() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
                        xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
                        xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
                        xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0">
<office:automatic-styles>
    <style:style style:name="ce1" style:family="table-cell">
        <style:text-properties fo:font-weight="bold"/>
    </style:style>
</office:automatic-styles>
<office:body>
<office:spreadsheet>
<table:table table:name="' . htmlspecialchars($this->sheets[0]['name'] ?? 'Sheet1') . '">';
        
        if (isset($this->sheets[0])) {
            foreach ($this->sheets[0]['data'] as $row_idx => $row) {
                $xml .= '<table:table-row>';
                foreach ($row as $cell) {
                    $xml .= '<table:table-cell office:value-type="string" text:style-name="ce1">
                        <text:p>' . htmlspecialchars((string)$cell) . '</text:p>
                    </table:table-cell>';
                }
                $xml .= '</table:table-row>';
            }
        }
        
        $xml .= '</table:table>
</office:spreadsheet>
</office:body>
</office:document-content>';
        
        return $xml;
    }
    
    private function getODSMeta() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<office:document-meta xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
                     xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0">
<office:meta>
    <meta:title>' . htmlspecialchars($this->metadata['title']) . '</meta:title>
    <meta:subject>' . htmlspecialchars($this->metadata['subject']) . '</meta:subject>
    <meta:creator>' . htmlspecialchars($this->metadata['creator']) . '</meta:creator>
    <meta:description>' . htmlspecialchars($this->metadata['description']) . '</meta:description>
</office:meta>
</office:document-meta>';
    }
    
    private function getODSManifest() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">
    <manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>
    <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
    <manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>
</manifest:manifest>';
    }
    
    private function getCellReference($col, $row) {
        $col_letter = '';
        $col_num = $col + 1;
        
        while ($col_num > 0) {
            $remainder = ($col_num - 1) % 26;
            $col_letter = chr(65 + $remainder) . $col_letter;
            $col_num = intval(($col_num - $remainder) / 26);
        }
        
        return $col_letter . ($row + 1);
    }
}
?>
