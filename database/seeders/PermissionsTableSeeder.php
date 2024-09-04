<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'view_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:04',
                'updated_at' => '2024-09-04 14:12:04',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'view_any_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'create_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'update_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'restore_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'restore_any_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'replicate_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:05',
                'updated_at' => '2024-09-04 14:12:05',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'reorder_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'delete_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'delete_any_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'force_delete_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'force_delete_any_cart::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'view_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'view_any_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:06',
                'updated_at' => '2024-09-04 14:12:06',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'create_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'update_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'restore_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'restore_any_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'replicate_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'reorder_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'delete_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:07',
                'updated_at' => '2024-09-04 14:12:07',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'delete_any_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'force_delete_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'force_delete_any_customer',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'view_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'view_any_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'create_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'update_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:08',
                'updated_at' => '2024-09-04 14:12:08',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'restore_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:09',
                'updated_at' => '2024-09-04 14:12:09',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'restore_any_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:09',
                'updated_at' => '2024-09-04 14:12:09',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'replicate_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:09',
                'updated_at' => '2024-09-04 14:12:09',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'reorder_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:09',
                'updated_at' => '2024-09-04 14:12:09',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'delete_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            33 => 
            array (
                'id' => 34,
                'name' => 'delete_any_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            34 => 
            array (
                'id' => 35,
                'name' => 'force_delete_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            35 => 
            array (
                'id' => 36,
                'name' => 'force_delete_any_group',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            36 => 
            array (
                'id' => 37,
                'name' => 'view_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            37 => 
            array (
                'id' => 38,
                'name' => 'view_any_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:10',
                'updated_at' => '2024-09-04 14:12:10',
            ),
            38 => 
            array (
                'id' => 39,
                'name' => 'create_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            39 => 
            array (
                'id' => 40,
                'name' => 'update_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            40 => 
            array (
                'id' => 41,
                'name' => 'restore_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            41 => 
            array (
                'id' => 42,
                'name' => 'restore_any_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            42 => 
            array (
                'id' => 43,
                'name' => 'replicate_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            43 => 
            array (
                'id' => 44,
                'name' => 'reorder_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:11',
                'updated_at' => '2024-09-04 14:12:11',
            ),
            44 => 
            array (
                'id' => 45,
                'name' => 'delete_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            45 => 
            array (
                'id' => 46,
                'name' => 'delete_any_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            46 => 
            array (
                'id' => 47,
                'name' => 'force_delete_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            47 => 
            array (
                'id' => 48,
                'name' => 'force_delete_any_invoice',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            48 => 
            array (
                'id' => 49,
                'name' => 'view_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            49 => 
            array (
                'id' => 50,
                'name' => 'view_any_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:12',
                'updated_at' => '2024-09-04 14:12:12',
            ),
            50 => 
            array (
                'id' => 51,
                'name' => 'create_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:13',
                'updated_at' => '2024-09-04 14:12:13',
            ),
            51 => 
            array (
                'id' => 52,
                'name' => 'update_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:13',
                'updated_at' => '2024-09-04 14:12:13',
            ),
            52 => 
            array (
                'id' => 53,
                'name' => 'restore_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:13',
                'updated_at' => '2024-09-04 14:12:13',
            ),
            53 => 
            array (
                'id' => 54,
                'name' => 'restore_any_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:13',
                'updated_at' => '2024-09-04 14:12:13',
            ),
            54 => 
            array (
                'id' => 55,
                'name' => 'replicate_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:13',
                'updated_at' => '2024-09-04 14:12:13',
            ),
            55 => 
            array (
                'id' => 56,
                'name' => 'reorder_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            56 => 
            array (
                'id' => 57,
                'name' => 'delete_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            57 => 
            array (
                'id' => 58,
                'name' => 'delete_any_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            58 => 
            array (
                'id' => 59,
                'name' => 'force_delete_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            59 => 
            array (
                'id' => 60,
                'name' => 'force_delete_any_order',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            60 => 
            array (
                'id' => 61,
                'name' => 'view_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:14',
                'updated_at' => '2024-09-04 14:12:14',
            ),
            61 => 
            array (
                'id' => 62,
                'name' => 'view_any_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:15',
                'updated_at' => '2024-09-04 14:12:15',
            ),
            62 => 
            array (
                'id' => 63,
                'name' => 'create_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:15',
                'updated_at' => '2024-09-04 14:12:15',
            ),
            63 => 
            array (
                'id' => 64,
                'name' => 'update_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:15',
                'updated_at' => '2024-09-04 14:12:15',
            ),
            64 => 
            array (
                'id' => 65,
                'name' => 'restore_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:15',
                'updated_at' => '2024-09-04 14:12:15',
            ),
            65 => 
            array (
                'id' => 66,
                'name' => 'restore_any_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:15',
                'updated_at' => '2024-09-04 14:12:15',
            ),
            66 => 
            array (
                'id' => 67,
                'name' => 'replicate_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            67 => 
            array (
                'id' => 68,
                'name' => 'reorder_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            68 => 
            array (
                'id' => 69,
                'name' => 'delete_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            69 => 
            array (
                'id' => 70,
                'name' => 'delete_any_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            70 => 
            array (
                'id' => 71,
                'name' => 'force_delete_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            71 => 
            array (
                'id' => 72,
                'name' => 'force_delete_any_order::item',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:16',
                'updated_at' => '2024-09-04 14:12:16',
            ),
            72 => 
            array (
                'id' => 73,
                'name' => 'view_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:17',
                'updated_at' => '2024-09-04 14:12:17',
            ),
            73 => 
            array (
                'id' => 74,
                'name' => 'view_any_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:17',
                'updated_at' => '2024-09-04 14:12:17',
            ),
            74 => 
            array (
                'id' => 75,
                'name' => 'create_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:17',
                'updated_at' => '2024-09-04 14:12:17',
            ),
            75 => 
            array (
                'id' => 76,
                'name' => 'update_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:17',
                'updated_at' => '2024-09-04 14:12:17',
            ),
            76 => 
            array (
                'id' => 77,
                'name' => 'restore_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:17',
                'updated_at' => '2024-09-04 14:12:17',
            ),
            77 => 
            array (
                'id' => 78,
                'name' => 'restore_any_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:18',
                'updated_at' => '2024-09-04 14:12:18',
            ),
            78 => 
            array (
                'id' => 79,
                'name' => 'replicate_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:18',
                'updated_at' => '2024-09-04 14:12:18',
            ),
            79 => 
            array (
                'id' => 80,
                'name' => 'reorder_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:18',
                'updated_at' => '2024-09-04 14:12:18',
            ),
            80 => 
            array (
                'id' => 81,
                'name' => 'delete_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:18',
                'updated_at' => '2024-09-04 14:12:18',
            ),
            81 => 
            array (
                'id' => 82,
                'name' => 'delete_any_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            82 => 
            array (
                'id' => 83,
                'name' => 'force_delete_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            83 => 
            array (
                'id' => 84,
                'name' => 'force_delete_any_product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            84 => 
            array (
                'id' => 85,
                'name' => 'view_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            85 => 
            array (
                'id' => 86,
                'name' => 'view_any_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            86 => 
            array (
                'id' => 87,
                'name' => 'create_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:19',
                'updated_at' => '2024-09-04 14:12:19',
            ),
            87 => 
            array (
                'id' => 88,
                'name' => 'update_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            88 => 
            array (
                'id' => 89,
                'name' => 'restore_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            89 => 
            array (
                'id' => 90,
                'name' => 'restore_any_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            90 => 
            array (
                'id' => 91,
                'name' => 'replicate_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            91 => 
            array (
                'id' => 92,
                'name' => 'reorder_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            92 => 
            array (
                'id' => 93,
                'name' => 'delete_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:20',
                'updated_at' => '2024-09-04 14:12:20',
            ),
            93 => 
            array (
                'id' => 94,
                'name' => 'delete_any_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:21',
                'updated_at' => '2024-09-04 14:12:21',
            ),
            94 => 
            array (
                'id' => 95,
                'name' => 'force_delete_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:21',
                'updated_at' => '2024-09-04 14:12:21',
            ),
            95 => 
            array (
                'id' => 96,
                'name' => 'force_delete_any_product::category',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:21',
                'updated_at' => '2024-09-04 14:12:21',
            ),
            96 => 
            array (
                'id' => 97,
                'name' => 'view_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:21',
                'updated_at' => '2024-09-04 14:12:21',
            ),
            97 => 
            array (
                'id' => 98,
                'name' => 'view_any_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:21',
                'updated_at' => '2024-09-04 14:12:21',
            ),
            98 => 
            array (
                'id' => 99,
                'name' => 'create_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            99 => 
            array (
                'id' => 100,
                'name' => 'update_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            100 => 
            array (
                'id' => 101,
                'name' => 'restore_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            101 => 
            array (
                'id' => 102,
                'name' => 'restore_any_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            102 => 
            array (
                'id' => 103,
                'name' => 'replicate_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            103 => 
            array (
                'id' => 104,
                'name' => 'reorder_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:22',
                'updated_at' => '2024-09-04 14:12:22',
            ),
            104 => 
            array (
                'id' => 105,
                'name' => 'delete_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            105 => 
            array (
                'id' => 106,
                'name' => 'delete_any_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            106 => 
            array (
                'id' => 107,
                'name' => 'force_delete_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            107 => 
            array (
                'id' => 108,
                'name' => 'force_delete_any_product::rating',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            108 => 
            array (
                'id' => 109,
                'name' => 'view_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            109 => 
            array (
                'id' => 110,
                'name' => 'view_any_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:23',
                'updated_at' => '2024-09-04 14:12:23',
            ),
            110 => 
            array (
                'id' => 111,
                'name' => 'create_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            111 => 
            array (
                'id' => 112,
                'name' => 'update_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            112 => 
            array (
                'id' => 113,
                'name' => 'restore_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            113 => 
            array (
                'id' => 114,
                'name' => 'restore_any_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            114 => 
            array (
                'id' => 115,
                'name' => 'replicate_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            115 => 
            array (
                'id' => 116,
                'name' => 'reorder_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:24',
                'updated_at' => '2024-09-04 14:12:24',
            ),
            116 => 
            array (
                'id' => 117,
                'name' => 'delete_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            117 => 
            array (
                'id' => 118,
                'name' => 'delete_any_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            118 => 
            array (
                'id' => 119,
                'name' => 'force_delete_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            119 => 
            array (
                'id' => 120,
                'name' => 'force_delete_any_product::review',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            120 => 
            array (
                'id' => 121,
                'name' => 'view_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            121 => 
            array (
                'id' => 122,
                'name' => 'view_any_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:25',
                'updated_at' => '2024-09-04 14:12:25',
            ),
            122 => 
            array (
                'id' => 123,
                'name' => 'create_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            123 => 
            array (
                'id' => 124,
                'name' => 'update_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            124 => 
            array (
                'id' => 125,
                'name' => 'restore_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            125 => 
            array (
                'id' => 126,
                'name' => 'restore_any_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            126 => 
            array (
                'id' => 127,
                'name' => 'replicate_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            127 => 
            array (
                'id' => 128,
                'name' => 'reorder_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:26',
                'updated_at' => '2024-09-04 14:12:26',
            ),
            128 => 
            array (
                'id' => 129,
                'name' => 'delete_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            129 => 
            array (
                'id' => 130,
                'name' => 'delete_any_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            130 => 
            array (
                'id' => 131,
                'name' => 'force_delete_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            131 => 
            array (
                'id' => 132,
                'name' => 'force_delete_any_product::tag',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            132 => 
            array (
                'id' => 133,
                'name' => 'view_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            133 => 
            array (
                'id' => 134,
                'name' => 'view_any_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:27',
                'updated_at' => '2024-09-04 14:12:27',
            ),
            134 => 
            array (
                'id' => 135,
                'name' => 'create_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:28',
                'updated_at' => '2024-09-04 14:12:28',
            ),
            135 => 
            array (
                'id' => 136,
                'name' => 'update_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:28',
                'updated_at' => '2024-09-04 14:12:28',
            ),
            136 => 
            array (
                'id' => 137,
                'name' => 'restore_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:28',
                'updated_at' => '2024-09-04 14:12:28',
            ),
            137 => 
            array (
                'id' => 138,
                'name' => 'restore_any_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:28',
                'updated_at' => '2024-09-04 14:12:28',
            ),
            138 => 
            array (
                'id' => 139,
                'name' => 'replicate_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:29',
                'updated_at' => '2024-09-04 14:12:29',
            ),
            139 => 
            array (
                'id' => 140,
                'name' => 'reorder_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:29',
                'updated_at' => '2024-09-04 14:12:29',
            ),
            140 => 
            array (
                'id' => 141,
                'name' => 'delete_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:29',
                'updated_at' => '2024-09-04 14:12:29',
            ),
            141 => 
            array (
                'id' => 142,
                'name' => 'delete_any_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:30',
                'updated_at' => '2024-09-04 14:12:30',
            ),
            142 => 
            array (
                'id' => 143,
                'name' => 'force_delete_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:30',
                'updated_at' => '2024-09-04 14:12:30',
            ),
            143 => 
            array (
                'id' => 144,
                'name' => 'force_delete_any_simple::product',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:30',
                'updated_at' => '2024-09-04 14:12:30',
            ),
            144 => 
            array (
                'id' => 145,
                'name' => 'page_EditProfile',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:30',
                'updated_at' => '2024-09-04 14:12:30',
            ),
            145 => 
            array (
                'id' => 146,
                'name' => 'page_PersonalAccessTokensPage',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:31',
                'updated_at' => '2024-09-04 14:12:31',
            ),
            146 => 
            array (
                'id' => 147,
                'name' => 'page_UpdateProfileInformationPage',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:12:31',
                'updated_at' => '2024-09-04 14:12:31',
            ),
            147 => 
            array (
                'id' => 148,
                'name' => 'view_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:23',
                'updated_at' => '2024-09-04 14:20:23',
            ),
            148 => 
            array (
                'id' => 149,
                'name' => 'view_any_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:23',
                'updated_at' => '2024-09-04 14:20:23',
            ),
            149 => 
            array (
                'id' => 150,
                'name' => 'create_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:23',
                'updated_at' => '2024-09-04 14:20:23',
            ),
            150 => 
            array (
                'id' => 151,
                'name' => 'update_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:23',
                'updated_at' => '2024-09-04 14:20:23',
            ),
            151 => 
            array (
                'id' => 152,
                'name' => 'restore_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:23',
                'updated_at' => '2024-09-04 14:20:23',
            ),
            152 => 
            array (
                'id' => 153,
                'name' => 'restore_any_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            153 => 
            array (
                'id' => 154,
                'name' => 'replicate_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            154 => 
            array (
                'id' => 155,
                'name' => 'reorder_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            155 => 
            array (
                'id' => 156,
                'name' => 'delete_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            156 => 
            array (
                'id' => 157,
                'name' => 'delete_any_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            157 => 
            array (
                'id' => 158,
                'name' => 'force_delete_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            158 => 
            array (
                'id' => 159,
                'name' => 'force_delete_any_coupon',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:24',
                'updated_at' => '2024-09-04 14:20:24',
            ),
            159 => 
            array (
                'id' => 160,
                'name' => 'view_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            160 => 
            array (
                'id' => 161,
                'name' => 'view_any_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            161 => 
            array (
                'id' => 162,
                'name' => 'create_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            162 => 
            array (
                'id' => 163,
                'name' => 'update_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            163 => 
            array (
                'id' => 164,
                'name' => 'restore_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            164 => 
            array (
                'id' => 165,
                'name' => 'restore_any_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:25',
                'updated_at' => '2024-09-04 14:20:25',
            ),
            165 => 
            array (
                'id' => 166,
                'name' => 'replicate_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
            166 => 
            array (
                'id' => 167,
                'name' => 'reorder_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
            167 => 
            array (
                'id' => 168,
                'name' => 'delete_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
            168 => 
            array (
                'id' => 169,
                'name' => 'delete_any_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
            169 => 
            array (
                'id' => 170,
                'name' => 'force_delete_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
            170 => 
            array (
                'id' => 171,
                'name' => 'force_delete_any_store',
                'guard_name' => 'web',
                'created_at' => '2024-09-04 14:20:26',
                'updated_at' => '2024-09-04 14:20:26',
            ),
        ));
        
        
    }
}