import React , {Component} from 'react';
import { ClipLoader } from 'react-spinners';
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import "react-tabs/style/react-tabs.css";
import OrdersList from './OrdersList/OrdersList';

class Orders extends Component {
    state = {
    }

    render() {
        return (
            <Tabs>
                <TabList>
                    <Tab>در حال بررسی</Tab>
                    <Tab>تایید سفارش</Tab>
                    <Tab>جمع آوری سفارش</Tab>
                    <Tab>ارسال شده</Tab>
                    <Tab>تحویل داده شده</Tab>
                </TabList>

                <TabPanel>
                    <OrdersList status="checking" listName="در حال بررسی"/>
                </TabPanel>
                <TabPanel>
                    <OrdersList status="confirmed" listName="تایید سفارش"/>
                </TabPanel>
                <TabPanel>
                    <OrdersList status="collecting" listName="جمع آوری سفارش"/>
                </TabPanel>
                <TabPanel>
                    <OrdersList status="sent" listName="ارسال شده"/>
                </TabPanel>
                <TabPanel>
                    <OrdersList status="received" listName="تحویل داده شده"/>
                </TabPanel>
            </Tabs>
        )
    }
};

export default Orders;