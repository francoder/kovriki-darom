urlDispatcher.addRoutes({
    'module.services': NETCAT_PATH + 'modules/services/admin/?controller=settings&action=index',
    'module.services.settings': NETCAT_PATH + 'modules/services/admin/?controller=settings&action=index',
    'module.services.metrika': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=index',
    'module.services.metrika.counter.update_status': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=update_status&counter_id=%1',
    'module.services.metrika.counter.add': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=edit_counter',
    'module.services.metrika.counter.edit': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=edit_counter&counter_id=%1',
    'module.services.metrika.stat.traffic': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=stat&tab=traffic&counter_id=%1',
    'module.services.metrika.stat.sources': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=stat&tab=sources&counter_id=%1',
    'module.services.metrika.stat.users': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=stat&tab=users&counter_id=%1',
    'module.services.metrika.stat.content': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=stat&tab=content&counter_id=%1',
    'module.services.metrika.stat.tech': NETCAT_PATH + 'modules/services/admin/?controller=metrika&action=stat&tab=tech&counter_id=%1',
      
      
    1: '' // dummy entry
});