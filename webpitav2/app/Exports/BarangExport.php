<?php 
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class BarangExport implements FromCollection, WithHeadings
{
    protected $barang;

    public function __construct(Collection $barang)
    {
        $this->barang = $barang;
    }

    public function collection()
    {
        return $this->barang;
    }

    public function headings(): array
    {
        return ['id', 'nama_barang', 'stok', 'harga'];
    }
}
