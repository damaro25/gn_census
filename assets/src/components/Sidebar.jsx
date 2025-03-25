
import React from 'react';
import { Link, useNavigate } from 'react-router-dom';

const Sidebar = () => {

  return (
    <>
      <div class="pcoded-navigatio-lavel" data-i18n="nav.category.navigation" menu-title-theme="theme5" data-module="dash">Tableau de bord</div>
      <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true" data-module="dash">
        <li class="">
          <Link to="/another-page" className="nav-link"> 
            <span class="pcoded-micon"><i class="ti-desktop"></i></span>
            <span class="pcoded-mtext" data-i18n="nav.form-wizard.main">Dashboard</span>
            <span class="pcoded-mcaret"></span>
          </Link>
        </li>      
      </ul>
    </>
  );
};

export default Sidebar;
