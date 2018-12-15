import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import './Navigation.css';
import axios from 'axios';
import URLs from "../../../../URLs";

// const Navigation = (props) => (
class Navigation extends Component {
    state = {
        category: [],
    }

    componentDidMount() {
        let url = URLs.base_URL + URLs.get_products_category;
        axios.get(url)
            .then(response => {
                console.log("componentDidMount Navigation");
                console.log(response);
                this.setState({category: response.data});
            })
            .catch(err => {
                console.log("componentDidMount Navigation");console.log(err);
            });
    }
    render() {
        let categories = this.state.category.map((item) => {
            if(!( (item.product === "Boxes Enclosures Racks") || (item.product === "Cable Assemblies Coaxial Cables RF") ||
                    (item.product === "Cables Wires") || (item.product === "Connectors Interconnects") ||
                    (item.product === "Fans Thermal Management Thermal Heat Sinks") ||
                    (item.product === "Hardware Fasteners Accessories Board Supports") || (item.product === "Industrial Automation and Controls Machine Safety Light Curtains") ||
                    (item.product === "Industrial Controls Time Delay Relays") || (item.product === "Maker DIY Educational Wearables") ||
                    (item.product === "Power Supplies Board Mount") || (item.product === "Power Supplies External Internal Off Board") ||
                    (item.product === "Relays Solid State Relays") || (item.product === "Development Boards Kits Programmers") ||
                    (item.product === "Static Control ESD Clean Room Products Static Control Clothing") || (item.product === "Test and Measurement") ||
                    (item.product === "Switches Slide Switches") || (item.product === "Switches Toggle Switches") ||
                    (item.product === "Tools") || (item.product === "Uncategorized Miscellaneous")
                ) ) {
                let subcategory1, subcategory2;
                if (Object.keys(item.category).length > 0) {
                    let temp = Object.keys(item.category).map((property, j) => {
                        let tempLastCategory;
                        if (item.category[property].length > 0) {
                            tempLastCategory = item.category[property].map(subcategory => {
                                return (
                                    <li className="custom-dropdown-menu-item">
                                        <span className="custom-dropdown-menu-item-link">{subcategory}</span>
                                    </li>
                                )
                            });
                            if (tempLastCategory !== null) {
                                subcategory2 = <ul className="custom-dropdown-submenu product-list-nav">
                                    {tempLastCategory}
                                </ul>
                            }
                        }
                        return (
                            <li className="custom-dropdown-menu-item">
                                <span className="custom-dropdown-menu-item-link">{property}</span>
                                {subcategory2}
                            </li>
                        )
                    })
                    if (temp !== null) {
                        subcategory1 = <ul className="custom-dropdown-submenu product-list-nav">
                            {temp}
                        </ul>
                    }
                }

                return (
                    <li className="custom-dropdown-menu-item">
                   <span className="custom-dropdown-menu-item-link">
                       {/*<span className="dropdown-icon">&larr;</span>*/}
                       {item.product}</span>
                        {subcategory1}
                    </li>
                )
            }
        });
        return (
            <ul className="navbar-list-group text-light">
                <li className="navbar-list-item custom-dropdown" id="products">
                    <a className="navbar-list-item-link custom-dropdown-toggler">
                        محصولات
                    </a>
                    <ul className="custom-dropdown-menu product-list-nav">
                        {categories}
                    </ul>
                </li>
                {/*<li className="navbar-list-item">*/}
                {/*<a className="navbar-list-item-link">*/}
                {/*آموزش ها*/}
                {/*</a>*/}
                {/*</li>*/}
                {/*<li className="navbar-list-item">*/}
                {/*<a className="navbar-list-item-link">*/}
                {/*مجله*/}
                {/*</a>*/}
                {/*</li>*/}
                <li className="navbar-list-item">
                    <Link to="/online-conversion-calculator" className="navbar-list-item-link">محاسبه تبدیل</Link>
                </li>
                <li className="navbar-list-item">
                    <Link to="/articles" className="navbar-list-item-link">مجله</Link>
                </li>
                <li className="navbar-list-item">
                    <Link to="/videos" className="navbar-list-item-link">ویدیوها</Link>
                </li>
                {/*<li className="navbar-list-item">*/}
                {/*<a className="navbar-list-item-link">*/}
                {/*فوت پرینت ها*/}
                {/*</a>*/}
                {/*</li>*/}
            </ul>
        )
    }
}

export default Navigation;

