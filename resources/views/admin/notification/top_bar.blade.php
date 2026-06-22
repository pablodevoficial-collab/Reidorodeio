<ul class="nav nav-tabs mb-4 topTap breadcrumb-nav" role="tablist">
    <button class="breadcrumb-nav-close"><i class="las la-times"></i></button>
    <li class="nav-item {{ menuActive(['admin.setting.notification.global.email','admin.setting.notification.global.sms','admin.setting.notification.global.push']) }}" role="presentation">
        <a href="{{ route('admin.setting.notification.global.email') }}" class="nav-link text-dark" type="button">
            <i class="las la-globe"></i> Global Template
        </a>
    </li>
    <li class="nav-item {{ menuActive('admin.setting.notification.email') }}" role="presentation">
        <a href="{{ route('admin.setting.notification.email') }}" class="nav-link text-dark" type="button">
            <i class="las la-envelope"></i> Configuração de E-mail
        </a>
    </li>
    <li class="nav-item {{ menuActive('admin.setting.notification.sms') }}" role="presentation">
        <a href="{{ route('admin.setting.notification.sms') }}" class="nav-link text-dark" type="button">
            <i class="las la-sms"></i> Configuração de SMS
        </a>
    </li>
    <li class="nav-item {{ menuActive('admin.setting.notification.push') }}" role="presentation">
        <a href="{{ route('admin.setting.notification.push') }}" class="nav-link text-dark" type="button">
            <i class="las la-bell"></i> Configuração de Push
        </a>
    </li>
    <li class="nav-item {{ menuActive(['admin.setting.notification.templates','admin.setting.notification.template.edit']) }}" role="presentation">
        <a href="{{ route('admin.setting.notification.templates') }}" class="nav-link text-dark" type="button">
            <i class="las la-list"></i> Notificação Modelos
        </a>
    </li>
    <li class="nav-item {{ menuActive(['admin.setting.notification.rodeio.reminders.index','admin.setting.notification.rodeio.reminders.show']) }}" role="presentation">
        <a href="{{ route('admin.setting.notification.rodeio.reminders.index') }}" class="nav-link text-dark" type="button">
            <i class="las la-bell-slash"></i> Alertas de Rodeio
        </a>
    </li>
</ul>
