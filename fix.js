const fs = require('fs');
let content = fs.readFileSync('resources/views/frontend/frontend.blade.php', 'utf8');
const s1 = \        if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = canShowModalidades ? '' : 'none';
        if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = canShowModalidades ? '' : 'none';
        if (el.heroDesktopControls) el.heroDesktopControls.style.display = canShowModalidades ? '' : 'none';
        if (el.heroMobileControls) el.heroMobileControls.style.display = canShowModalidades ? '' : 'none';\;
const r1 = \        if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = canShowModalidades ? '' : 'none';
        if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = canShowModalidades ? '' : 'none';
        if (el.heroDesktopControls) el.heroDesktopControls.style.display = '';
        if (el.heroMobileControls) el.heroMobileControls.style.display = '';\;
const s2 = \            if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = 'none';
            if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = 'none';
            if (el.heroDesktopControls) el.heroDesktopControls.style.display = 'none';
            if (el.heroMobileControls) el.heroMobileControls.style.display = 'none';\;
const r2 = \            if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = 'none';
            if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = 'none';
            if (el.heroDesktopControls) el.heroDesktopControls.style.display = '';
            if (el.heroMobileControls) el.heroMobileControls.style.display = '';\;
if(content.includes(s1)) {
    content = content.replace(s1, r1);
    content = content.replace(s2, r2);
    fs.writeFileSync('resources/views/frontend/frontend.blade.php', content);
    console.log('Replaced successfully');
} else { console.log('not found'); }

