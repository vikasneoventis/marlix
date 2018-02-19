<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\setup;

use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Yosto\Slider\Helper\Constant;

/**
 * Class InstallSchema
 * @package Yosto\Slider\setup
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /*
         * Install slide table
         */
        $installer->getConnection()->dropTable($installer->getTable(Constant::SLIDE_TABLE));

        $table = $installer->getConnection()
            ->newTable($installer->getTable(Constant::SLIDE_TABLE))
            ->addColumn(
                'slide_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'primary' => true
                ],
                'Slide table primary key'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Slide Name'
            )
            ->addColumn(
                'height',
                Table::TYPE_TEXT,
                20,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'height of slide'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )
            ->addColumn(
                'items_number',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Number of items want to see on the screen'
            )
            ->addColumn(
                'margin',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Margin-right on item'
            )
            ->addColumn(
                'loop',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Infinity loop'
            )
            ->addColumn(
                'center',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Center item'
            )
            ->addColumn(
                'mouse_drag',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Mouse drag enable'
            )
            ->addColumn(
                'touch_drag',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Stage pull to edge'
            )
            ->addColumn(
                'free_drag',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Item pull to edge'
            )
            ->addColumn(
                'stage_padding',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Padding left and right on stage'
            )
            ->addColumn(
                'merge',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Merge items. looking items data-merge'
            )
            ->addColumn(
                'merge_fit',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Fit merged items'
            )
            ->addColumn(
                'auto_width',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Set non grid content'
            )
            ->addColumn(
                'start_position',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Start Position'

            )
            ->addColumn(
                'nav',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Show next/previous buttons'
            )
            ->addColumn(
                'rewind',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Go backwards when boundary has rached'
            )
            ->addColumn(
                'slide_by',
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => true
                ],
                'Navigation slide by x'
            )
            ->addColumn(
                'dots',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'show dots navigation'
            )
            ->addColumn(
                'dots_each',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Show dots each x item'
            )
            ->addColumn(
                'dot_data',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Used by data-dot content.'
            )
            ->addColumn(
                'lazy_load',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Lazy load images'
            )
            ->addColumn(
                'autoplay',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Autoplay'
            )
            ->addColumn(
                'autoplay_timeout',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Autoplay interval timeout'
            )
            ->addColumn(
                'autoplay_hover_pause',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Pause on mouse hover'
            )
            ->addColumn(
                'smart_speed',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Speed calculate'
            )
            ->addColumn(
                'fluid_speed',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Speed calculate'
            )
            ->addColumn(
                'autoplay_speed',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Autoplay speed'
            )
            ->addColumn(
                'nav_speed',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Navigation speed'
            )
            ->addColumn(
                'dots_speed',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Pagination speed'
            )
            ->addColumn(
                'drag_end_speed',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Drag end speed'
            )
            ->addColumn(
                'callbacks',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Enable callback events'
            )
            ->addColumn(
                'responsive_refresh_rate',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Responsive refresh rate'
            )
            ->addColumn(
                'responsive_base_element',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ],
                'Set on any dom element'
            )
            ->addColumn(
                'video',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                ],
                'Enable fetching Youtuber/vimeo/vzaar videos'
            )
            ->addColumn(
                'video_height',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true,
                ],
                'Video height'
            )
            ->addColumn(
                'video_width',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true,
                ],
                'Set video width'
            )
            ->addColumn(
                'animate_out',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true,
                ],
                'Class for css 3 animation out'
            )
            ->addColumn(
                'animate_in',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true,
                ],
                'Class for css 3 animation in'
            )
            ->addColumn(
                'fallback_easing',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true,
                ],
                'Easing for css 2, default swing'
            )
            ->setComment(Constant::SLIDE_TABLE_COMMENT)
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);


        /*
         * Install image table
         */

        $installer->getConnection()->dropTable($installer->getTable(Constant::IMAGE_TABLE));

        $table = $installer->getConnection()->newTable($installer->getTable(Constant::IMAGE_TABLE))
            ->addColumn(
                'image_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'primary' => true,
                ],
                'Image Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Image name'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Title'
            )
            ->addColumn(
                'subtitle',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Subtitle'
            )
            ->addColumn(
                'image_html',
                Table::TYPE_TEXT,
                500,
                [
                    'nullable' => false
                ],
                'Image Html'
            )
            ->addColumn(
                'href',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Href link'
            )
            ->addColumn(
                'button_title',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true,
                ],
                'Button title'
            )
            ->addColumn(
                'content_position',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Content Position'
            )
            ->addColumn(
                'content_width',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Content Width'
            )
            ->addColumn(
                'title_font_size',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Title font size'
            )
            ->addColumn(
                'title_color',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Title color'
            )
            ->addColumn(
                'subtitle_color',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Subtitle color'
            )
            ->addColumn(
                'subtitle_font_size',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Subtitle font size'
            )
            ->addColumn(
                'button_title_color',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Button Title Color'
            )
            ->addColumn(
                'button_font_size',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Button font size'
            )
            ->addColumn(
                'button_background_color',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Button Background Color'
            )
            ->addColumn(
                'title_effect',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ],
                'Title Effect'
            )
            ->addColumn(
                'subtitle_effect',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ],
                'Subtitle Effect'
            )
            ->addColumn(
                'button_effect',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ],
                'Button Effect'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Image Order'
            )
            ->setComment(Constant::IMAGE_TABLE_COMMENT)
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->dropTable($installer->getTable(Constant::SLIDE_IMAGE_TABLE));
        $table = $installer->getConnection()->newTable($installer->getTable(Constant::SLIDE_IMAGE_TABLE))
            ->addColumn(
                Constant::SLIDE_IMAGE_TABLE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'primary' => true
                ],
                'Slide-Image table id'
            )
            ->addColumn(
                'image_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Image Id reference from Image table'
            )
            ->addColumn(
                'slide_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Slide id referece from Slide Table'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Whether or not image is active in slide'
            )
            ->addIndex(
                $installer->getIdxName(Constant::IMAGE_TABLE, [Constant::IMAGE_TABLE_ID]),
                Constant::IMAGE_TABLE_ID
            )
            ->addForeignKey(
                $installer->getFkName(Constant::SLIDE_IMAGE_TABLE, Constant::SLIDE_TABLE_ID, Constant::SLIDE_TABLE, Constant::SLIDE_TABLE_ID),
                Constant::SLIDE_TABLE_ID,
                $installer->getTable(Constant::SLIDE_TABLE),
                Constant::SLIDE_TABLE_ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(Constant::SLIDE_IMAGE_TABLE, Constant::IMAGE_TABLE_ID, Constant::IMAGE_TABLE, Constant::IMAGE_TABLE_ID),
                Constant::IMAGE_TABLE_ID,
                $installer->getTable(Constant::IMAGE_TABLE),
                Constant::IMAGE_TABLE_ID,
                Table::ACTION_CASCADE
            )
            ->setComment(Constant::SLIDE_IMAGE_TABLE_COMMENT)
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }

}