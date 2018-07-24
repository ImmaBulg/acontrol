<?php

use yii\db\Schema;

class m160301_110928_alter_created_modified_columns extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropForeignKey('FK_api_key_created_by', 'api_key');
		$this->dropForeignKey('FK_api_key_modified_by', 'api_key');
		$this->addForeignKey('FK_api_key_created_by', 'api_key', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_api_key_modified_by', 'api_key', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_log_created_by', 'log');
		$this->dropForeignKey('FK_log_modified_by', 'log');
		$this->addForeignKey('FK_log_created_by', 'log', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_log_modified_by', 'log', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_created_by', 'meter');
		$this->dropForeignKey('FK_meter_modified_by', 'meter');
		$this->addForeignKey('FK_meter_created_by', 'meter', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_modified_by', 'meter', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_channel_created_by', 'meter_channel');
		$this->dropForeignKey('FK_meter_channel_modified_by', 'meter_channel');
		$this->addForeignKey('FK_meter_channel_created_by', 'meter_channel', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_modified_by', 'meter_channel', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_channel_group_created_by', 'meter_channel_group');
		$this->dropForeignKey('FK_meter_channel_group_modified_by', 'meter_channel_group');
		$this->addForeignKey('FK_meter_channel_group_created_by', 'meter_channel_group', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_modified_by', 'meter_channel_group', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_channel_group_item_created_by', 'meter_channel_group_item');
		$this->dropForeignKey('FK_meter_channel_group_item_modified_by', 'meter_channel_group_item');
		$this->addForeignKey('FK_meter_channel_group_item_created_by', 'meter_channel_group_item', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_item_modified_by', 'meter_channel_group_item', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	
		$this->dropForeignKey('FK_meter_raw_data_created_by', 'meter_raw_data');
		$this->dropForeignKey('FK_meter_raw_data_modified_by', 'meter_raw_data');
		$this->addForeignKey('FK_meter_raw_data_created_by', 'meter_raw_data', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_modified_by', 'meter_raw_data', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_raw_data_schedule_created_by', 'meter_raw_data_schedule');
		$this->dropForeignKey('FK_meter_raw_data_schedule_modified_by', 'meter_raw_data_schedule');
		$this->addForeignKey('FK_meter_raw_data_schedule_created_by', 'meter_raw_data_schedule', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_schedule_modified_by', 'meter_raw_data_schedule', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	
		$this->dropForeignKey('FK_meter_subchannel_created_by', 'meter_subchannel');
		$this->dropForeignKey('FK_meter_subchannel_modified_by', 'meter_subchannel');
		$this->addForeignKey('FK_meter_subchannel_created_by', 'meter_subchannel', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_subchannel_modified_by', 'meter_subchannel', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_meter_type_created_by', 'meter_type');
		$this->dropForeignKey('FK_meter_type_modified_by', 'meter_type');
		$this->addForeignKey('FK_meter_type_created_by', 'meter_type', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_type_modified_by', 'meter_type', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_rate_created_by', 'rate');
		$this->dropForeignKey('FK_rate_modified_by', 'rate');
		$this->addForeignKey('FK_rate_created_by', 'rate', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rate_modified_by', 'rate', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_rate_type_created_by', 'rate_type');
		$this->dropForeignKey('FK_rate_type_modified_by', 'rate_type');
		$this->addForeignKey('FK_rate_type_created_by', 'rate_type', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rate_type_modified_by', 'rate_type', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_report_created_by', 'report');
		$this->dropForeignKey('FK_report_modified_by', 'report');
		$this->addForeignKey('FK_report_created_by', 'report', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_report_modified_by', 'report', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_report_file_created_by', 'report_file');
		$this->dropForeignKey('FK_report_file_modified_by', 'report_file');
		$this->addForeignKey('FK_report_file_created_by', 'report_file', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_report_file_modified_by', 'report_file', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_report_template_created_by', 'report_template');
		$this->dropForeignKey('FK_report_template_modified_by', 'report_template');
		$this->addForeignKey('FK_report_template_created_by', 'report_template', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_report_template_modified_by', 'report_template', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_rule_fixed_load_created_by', 'rule_fixed_load');
		$this->dropForeignKey('FK_rule_fixed_load_modified_by', 'rule_fixed_load');
		$this->addForeignKey('FK_rule_fixed_load_created_by', 'rule_fixed_load', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rule_fixed_load_modified_by', 'rule_fixed_load', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_rule_group_load_created_by', 'rule_group_load');
		$this->dropForeignKey('FK_rule_group_load_modified_by', 'rule_group_load');
		$this->addForeignKey('FK_rule_group_load_created_by', 'rule_group_load', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_modified_by', 'rule_group_load', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_rule_single_channel_created_by', 'rule_single_channel');
		$this->dropForeignKey('FK_rule_single_channel_modified_by', 'rule_single_channel');
		$this->addForeignKey('FK_rule_single_channel_created_by', 'rule_single_channel', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rule_single_channel_modified_by', 'rule_single_channel', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_site_created_by', 'site');
		$this->dropForeignKey('FK_site_modified_by', 'site');
		$this->addForeignKey('FK_site_created_by', 'site', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_site_modified_by', 'site', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_site_billing_setting_created_by', 'site_billing_setting');
		$this->dropForeignKey('FK_site_billing_setting_modified_by', 'site_billing_setting');
		$this->addForeignKey('FK_site_billing_setting_created_by', 'site_billing_setting', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_site_billing_setting_modified_by', 'site_billing_setting', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_site_contact_created_by', 'site_contact');
		$this->dropForeignKey('FK_site_contact_modified_by', 'site_contact');
		$this->addForeignKey('FK_site_contact_created_by', 'site_contact', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_site_contact_modified_by', 'site_contact', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_site_meter_tree_created_by', 'site_meter_tree');
		$this->dropForeignKey('FK_site_meter_tree_modified_by', 'site_meter_tree');
		$this->addForeignKey('FK_site_meter_tree_created_by', 'site_meter_tree', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_modified_by', 'site_meter_tree', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_task_created_by', 'task');
		$this->dropForeignKey('FK_task_modified_by', 'task');
		$this->addForeignKey('FK_task_created_by', 'task', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_modified_by', 'task', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_task_comment_created_by', 'task_comment');
		$this->dropForeignKey('FK_task_comment_modified_by', 'task_comment');
		$this->addForeignKey('FK_task_comment_created_by', 'task_comment', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_comment_modified_by', 'task_comment', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_tenant_created_by', 'tenant');
		$this->dropForeignKey('FK_tenant_modified_by', 'tenant');
		$this->addForeignKey('FK_tenant_created_by', 'tenant', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_modified_by', 'tenant', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_tenant_billing_setting_created_by', 'tenant_billing_setting');
		$this->dropForeignKey('FK_tenant_billing_setting_modified_by', 'tenant_billing_setting');
		$this->addForeignKey('FK_tenant_billing_setting_created_by', 'tenant_billing_setting', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_billing_setting_modified_by', 'tenant_billing_setting', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	
		$this->dropForeignKey('FK_tenant_contact_created_by', 'tenant_contact');
		$this->dropForeignKey('FK_tenant_contact_modified_by', 'tenant_contact');
		$this->addForeignKey('FK_tenant_contact_created_by', 'tenant_contact', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_contact_modified_by', 'tenant_contact', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_tenant_group_created_by', 'tenant_group');
		$this->dropForeignKey('FK_tenant_group_modified_by', 'tenant_group');
		$this->addForeignKey('FK_tenant_group_created_by', 'tenant_group', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_modified_by', 'tenant_group', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_tenant_group_item_created_by', 'tenant_group_item');
		$this->dropForeignKey('FK_tenant_group_item_modified_by', 'tenant_group_item');
		$this->addForeignKey('FK_tenant_group_item_created_by', 'tenant_group_item', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_item_modified_by', 'tenant_group_item', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_tenant_report_created_by', 'tenant_report');
		$this->dropForeignKey('FK_tenant_report_modified_by', 'tenant_report');
		$this->addForeignKey('FK_tenant_report_created_by', 'tenant_report', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_tenant_report_modified_by', 'tenant_report', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_text_created_by', 'text');
		$this->dropForeignKey('FK_text_modified_by', 'text');
		$this->addForeignKey('FK_text_created_by', 'text', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_text_modified_by', 'text', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_text_item_created_by', 'text_item');
		$this->dropForeignKey('FK_text_item_modified_by', 'text_item');
		$this->addForeignKey('FK_text_item_created_by', 'text_item', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_text_item_modified_by', 'text_item', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_user_alert_notification_created_by', 'user_alert_notification');
		$this->dropForeignKey('FK_user_alert_notification_modified_by', 'user_alert_notification');
		$this->addForeignKey('FK_user_alert_notification_created_by', 'user_alert_notification', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_user_alert_notification_modified_by', 'user_alert_notification', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	
		$this->dropForeignKey('FK_user_contact_created_by', 'user_contact');
		$this->dropForeignKey('FK_user_contact_modified_by', 'user_contact');
		$this->addForeignKey('FK_user_contact_created_by', 'user_contact', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_user_contact_modified_by', 'user_contact', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_user_owner_created_by', 'user_owner');
		$this->dropForeignKey('FK_user_owner_modified_by', 'user_owner');
		$this->addForeignKey('FK_user_owner_created_by', 'user_owner', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_modified_by', 'user_owner', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_user_profile_created_by', 'user_profile');
		$this->dropForeignKey('FK_user_profile_modified_by', 'user_profile');
		$this->addForeignKey('FK_user_profile_created_by', 'user_profile', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_user_profile_modified_by', 'user_profile', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->dropForeignKey('FK_vat_created_by', 'vat');
		$this->dropForeignKey('FK_vat_modified_by', 'vat');
		$this->addForeignKey('FK_vat_created_by', 'vat', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_vat_modified_by', 'vat', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}

	public function down()
	{
		
	}
}
