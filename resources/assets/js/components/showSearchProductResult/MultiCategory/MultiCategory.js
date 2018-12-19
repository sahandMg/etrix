import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

class MultiCategory extends Component {
    state = {
    }

    componentDidMount() {
    }

    render() {
        console.log("MultiCategory render");console.log(this.props.category);
        let categories = this.props.category.map((item,i) => {
            if (!( (item.category === "Boxes Enclosures Racks") || (item.category === "Cable Assemblies Coaxial Cables RF") ||
                    (item.category === "Cables Wires") || (item.category === "Connectors Interconnects") ||
                    (item.category === "Fans Thermal Management Thermal Heat Sinks") ||
                    (item.category === "Hardware Fasteners Accessories Board Supports") || (item.category === "Industrial Automation and Controls Machine Safety Light Curtains") ||
                    (item.category === "Industrial Controls Time Delay Relays") || (item.category === "Maker DIY Educational Wearables") ||
                    (item.category === "Power Supplies Board Mount") || (item.category === "Power Supplies External Internal Off Board") ||
                    (item.category === "Relays Solid State Relays") || (item.category === "Development Boards Kits Programmers") ||
                    (item.category === "Static Control ESD Clean Room Products Static Control Clothing") || (item.category === "Test and Measurement") ||
                    (item.category === "Switches Slide Switches") || (item.category === "Switches Toggle Switches") ||
                    (item.category === "Tools") || (item.category === "Uncategorized Miscellaneous")
                )) {
                console.log("MultiCategory subcategory1");console.log(item.subcategory);
                let subcategory1 = item.subcategory.map((sub,j) => {
                    return (
                        <li>
                            <Link to={"/search/"+this.props.cat+"/"+this.props.keyword+"&subcategory="+sub}>{sub.split("_").join(" ")}</Link>
                        </li>
                    )
                });

                return (
                    <div className="text-left margin-2" >
                        <h3>{item.category.split("_").join(" ")}</h3>
                        <ul className="margin-left-1">
                            {subcategory1}
                        </ul>
                    </div>
                )
            }
        });
        return (
            <div className="text-left margin-2">
                <h2 className="text-center">جست و جوی شما در دسته های زیر پیدا شده است</h2>
                {categories}
            </div>

        )
    }
}

export default MultiCategory;