import React, { Component } from 'react';
import SearchedProductPrice from '../SearchedProductPrice/SearchedProductPrice';

class ProductsTable extends Component {

    state = {
    }

    componentDidMount() {

    }

    render() {
        // dataTables
        let tableHeads = Object.keys(this.props.dataParts[0]).map((property) => {
            let temp = null;
            Object.keys(this.props.tableHeaderS).map((property2,i) => {
                if(this.props.tableHeaderS[property2] === property) {
                    temp = <th style={{minWidth: '110px'  }} key={property+'1'}>
                        <p>{property}</p><button className="btn btnHoverGreen" style={{margin: '2px' }}
                                                 onClick={() => {this.props.sort(property,'increase')}}>
                        <i className="fa fa-arrow-up" aria-hidden="true"></i></button>
                        <button className="btn btnHoverRed" onClick={() => {this.props.sort(property,'increase')}}>
                            <i className="fa fa-arrow-down" aria-hidden="true"></i>
                        </button>
                    </th> ;
                }
                return null;
            });
            if(temp === null) {
                if( !((property === "hd_image") || (property === "slug") || (property === "name") || (property === "type") || (property === "original") || (property === "part_status") || (property === "persian_name"))) {
                    return (   <th key={property + '2'} style={{paddingBottom: '52px'}}><p>{property}</p></th> );
                }
            } else {  return temp; }
        });
        // this.setInitialForPriceInput();
        let dataParts = this.props.dataParts.map((item, i) => {
            let entry = Object.keys(item).map((property, j) => {
                if(property === "unit_price") {
                    return ( <SearchedProductPrice key={property} keyword={item['manufacturer_part_number']} category={item[property]} openModal={this.props.onOpenModal} />)
                } else if(property === "ld_image") {
                    return ( <td key={property}><img alt={this.props.searchKey} src={item[property]} /></td> )
                } else if(property === "datasheet") {
                    return ( <td key={property}><a href={item[property]}><i className="fa fa-file-text" aria-hidden="true"></i></a></td> )
                } else if( !( (property === "hd_image") || (property === "slug") || (property === "name") || (property === "type") || (property === "original") || (property === "part_status") || (property === "persian_name"))) {
                    return ( <td key={property}>{item[property]}</td> )
                }

            });
            return (
                <tr key={i}>{entry}</tr>
            );
        });
        return (
            <table className="table table-striped">
                <thead>
                <tr>{tableHeads}</tr>
                </thead>
                <tbody style={{direction: "ltr"}}>{dataParts}</tbody>
            </table>
        )
    }
}

export default ProductsTable;

