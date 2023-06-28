<?php

$prefix = 'Przeslijmi.AgileDataXlsPlug';

return [

  /**
   * Operation - ReadFromXls.
   */
  'ReadFromXls.sourceName' => 'plik XLS (arkusz), jeden',
  'ReadFromXls.title' => 'czytanie z pliku XLS z arkusza',
  'ReadFromXls.fields.fileUri.name' => 'Adres pliku <strong>XLS</strong> do pobrania',
  'ReadFromXls.fields.fileUri.desc' => '',
  'ReadFromXls.fields.sheetName.name' => 'Nazwa arkusza do odczytania',
  'ReadFromXls.fields.sheetName.desc' => '',
  'ReadFromXls.fields.dataRef.name' => 'Zakres danych do odczytania',
  'ReadFromXls.fields.dataRef.desc' => 'Prawidłowe wypełnienie tego pola to podanie zakresu (w formacie XLS) komórek. Na przykład pierwszy wiersz pliku od kolumny A do kolumny D należy zapisać jako <code>A1:D1</code>. Można wskazać albo jeden (pierwszy) wiersz danych - wówczas program będzie czytać dalej aż trafi na koniec arkusza, lub puste wiersze. Można wskazać więcej niż jeden wiersz (np. <code>A1:D4</code>) - wówczas program odczyta wyłącznie wskazane dane.',
  'ReadFromXls.fields.cacheUri.name' => 'Lokalizacja cache dla czytanego pliku',
  'ReadFromXls.fields.cacheUri.desc' => 'Żeby przyspieszyć podczas wykonywania operacji program nie czyta pliku źródłowego XLS za każdym razem a jedynie jego kopię w formacie JSON. Program tylko sprawdza czy plik XLS ma tę samą datę edycji co poprzednio. Lokalizacja cache dla czytanego pliku wypełnia się automatycznie - ale możesz ją zmienić jeśli chcesz.',
  'ReadFromXls.fields.mapColumns.name' => 'Mapa kolumn',
  'ReadFromXls.fields.mapColumns.desc' => 'Jak mają być w zadaniu nazwane kolumny pobrane z pliku (i które kolumny wybrać).',
  'ReadFromXls.fields.mapColumns.sourceColumn.name' => 'kolumna z arkusza XLS',
  'ReadFromXls.fields.mapColumns.destinationProp.name' => 'kolumna w programie',
  'ReadFromXls.fields.mapColumns.dataType.name' => 'typ danych',
  'ReadFromXls.exc.XlsxFileUriDonoexException' => 'Pod wskazanym adresem nie istnieje plik XLS.',
  'ReadFromXls.exc.CacheFileUriInaccessible' => 'Wskazany adres do lokalizacji cache nie może być wykorzystywany do zapisu pliku - próba nie powiodła się.',
  'ReadFromXls.exc.XlsxFileReadingFopException' => 'Nie udało się wczytać pliku XLS. Najprawdopodobniej podana jest błędna nazwa arkusza, chociaż mógł też wystąpić inny błąd.',
  'ReadFromXls.exc.MapColumnsDestinationReuseException' => 'W prawej kolumnie mapy kolumn używasz dwukrotnie tej samej kolumny a to będzie prowadzić do nielogiczności operacji. Każdą kolumnę możesz użyć tylko raz.',

  /**
   * Operation - MassReadFromXls.
   */
  'MassReadFromXls.sourceName' => 'plik XLS (arkusz), wiele',
  'MassReadFromXls.title' => 'czytanie z folderu z plikami XLS',
  'MassReadFromXls.fields.dirUri.name' => 'Adres folderu z plikami <strong>XLS</strong> do pobrania',
  'MassReadFromXls.fields.dirUri.desc' => '',
  'MassReadFromXls.fields.sheetName.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.sheetName.name' ],
  'MassReadFromXls.fields.sheetName.desc' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.sheetName.desc' ],
  'MassReadFromXls.fields.dataRef.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.dataRef.name' ],
  'MassReadFromXls.fields.dataRef.desc' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.dataRef.desc' ],
  'MassReadFromXls.fields.cacheUri.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.cacheUri.name' ],
  'MassReadFromXls.fields.cacheUri.desc' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.cacheUri.desc' ],
  'MassReadFromXls.fields.mapColumns.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.mapColumns.name' ],
  'MassReadFromXls.fields.mapColumns.desc' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.mapColumns.desc' ],
  'MassReadFromXls.fields.mapColumns.sourceColumn.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.mapColumns.sourceColumn.name' ],
  'MassReadFromXls.fields.mapColumns.destinationProp.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.mapColumns.destinationProp.name' ],
  'MassReadFromXls.fields.mapColumns.dataType.name' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.mapColumns.dataType.name' ],
  'MassReadFromXls.exc.XlsxDirUriDonoexException' => 'Pod wskazanym adresem nie istnieje katalog, z którego mogłyby być pobrane pliki XLSX.',
  'MassReadFromXls.exc.MapColumnsDestinationReuseException' => [ 'redirect' => 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.exc.MapColumnsDestinationReuseException' ],

];
