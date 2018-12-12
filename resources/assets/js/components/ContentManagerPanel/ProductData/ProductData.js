import React , {Component} from 'react';
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import "react-tabs/style/react-tabs.css";
import AddDataPart from './AddDataPart/AddDataPart';
import EditProductData from './EditProductData/EditProductData';


class ProductData extends Component {
    render() {
        return (
            <div className="container" style={{marginTop: "2%"}}>
                <Tabs>
                    <TabList>
                        <Tab>تغییر</Tab>
                        <Tab>قطعه جدید</Tab>
                    </TabList>
                    <TabPanel>
                        <EditProductData/>
                    </TabPanel>
                    <TabPanel>
                        <AddDataPart/>
                    </TabPanel>
                </Tabs>
            </div>
        )
    }
}

export default ProductData;